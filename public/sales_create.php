<?php
require_once '../includes/auth.php'; 
require_login();
require_once '../config/db.php';

header('Content-Type: application/json');

try {
  // Datos del formulario
  $clientId = trim($_POST['Ipa_inv_client_ID'] ?? '');
  $clientAddr = trim($_POST['Ipa_inv_client_address'] ?? '');
  $itemsJson = $_POST['items_json'] ?? '[]';
  $items = json_decode($itemsJson, true);

  // Buscar nombre del cliente desde la base de datos
  $stmt = $conn->prepare("SELECT CONCAT(Ipa_client_firstname, ' ', Ipa_client_lastname) AS nombre FROM ipa_clients WHERE Ipa_client_ID=?");
  $stmt->execute([$clientId]);
  $clientName = $stmt->fetchColumn();

  if (!$clientId || !$clientName || empty($items)) {
    throw new Exception('Cliente e ítems son obligatorios.');
  }

  // Calcula total con precios actuales del stock
  $total = 0.0;
  $resolvedItems = []; // ítems con nombre, precio y subtotal

  foreach ($items as $it) {
    $stockId = (int)($it['stock_id'] ?? 0);
    $qty = (int)($it['qty'] ?? 0);
    if ($stockId <= 0 || $qty <= 0) continue;

    $stmt = $conn->prepare("SELECT Ipa_stock_name, Ipa_stock_price, Ipa_stock_onhand FROM ipa_stock WHERE Ipa_stock_ID=? AND Ipa_stock_status='activo'");
    $stmt->execute([$stockId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) throw new Exception("Producto $stockId no disponible.");

    if ((int)$row['Ipa_stock_onhand'] < $qty) {
      throw new Exception("Stock insuficiente para {$row['Ipa_stock_name']}.");
    }

    $price = (float)$row['Ipa_stock_price'];
    $amount = $price * $qty;
    $total += $amount;

    $resolvedItems[] = [
      'stock_id' => $stockId,
      'stock_name' => $row['Ipa_stock_name'],
      'qty' => $qty,
      'price' => $price,
      'amount' => $amount
    ];
  }

  if (empty($resolvedItems)) throw new Exception('No hay ítems válidos.');

  // Transacción
  $conn->beginTransaction();

  // Cabecera
  $stmt = $conn->prepare("
    INSERT INTO ipa_invoices (Ipa_inv_date, Ipa_inv_client_ID, Ipa_inv_client_name, Ipa_inv_client_address, Ipa_inv_amount, ipa_inv_status)
    VALUES (NOW(), ?, ?, ?, ?, ?)
  ");
  $ok = $stmt->execute([$clientId, $clientName, $clientAddr, $total, 'pagada']);
  if (!$ok) throw new Exception('No se pudo crear la factura.');

  $invoiceId = (int)$conn->lastInsertId();

  // Detalle + descuento de stock
  foreach ($resolvedItems as $idx => $it) {
    $invItemNo = sprintf('IT%06d', $invoiceId * 1000 + $idx + 1); // genera un ID único tipo IT000123

    // Detalle
    $stmt = $conn->prepare("
      INSERT INTO ipa_invoice_items (
        Ipa_invitem_no, Ipa_invitem_inv_no, Ipa_invitem_stock_ID, Ipa_invitem_stock_name,
        Ipa_invitem_qty, Ipa_invitem_stock_price, Ipa_invitem_stock_amount, Ipa_inv_status
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $ok = $stmt->execute([
      $invItemNo,
      (string)$invoiceId,
      (string)$it['stock_id'],
      $it['stock_name'],
      (string)$it['qty'],              // varchar(6)
      number_format($it['price'], 2, '.', ''),
      number_format($it['amount'], 2, '.', ''),
      'A'
    ]);
    if (!$ok) throw new Exception('No se pudo insertar detalle.');

    // Descuento de stock
    $stmt = $conn->prepare("
      UPDATE ipa_stock
      SET Ipa_stock_onhand = Ipa_stock_onhand - ?
      WHERE Ipa_stock_ID = ? AND Ipa_stock_onhand >= ?
    ");
    $ok = $stmt->execute([$it['qty'], $it['stock_id'], $it['qty']]);
    if (!$ok || $stmt->rowCount() === 0) {
      throw new Exception('No se pudo descontar stock (concurrencia/insuficiente).');
    }
  }

  $conn->commit();
  echo json_encode(['ok' => true, 'invoice_id' => $invoiceId, 'total' => $total]);

} catch (Exception $e) {
  if ($conn->inTransaction()) $conn->rollBack();
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}