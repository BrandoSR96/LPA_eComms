<?php
// public/invoices_demo.php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';

$conn->beginTransaction();
try {
  $conn->exec("INSERT IGNORE INTO ipa_clients (Ipa_client_ID, Ipa_client_firstname, Ipa_client_lastname, Ipa_client_address, Ipa_client_phone, Ipa_client_status) VALUES ('DEMO', 'Cliente', 'Demo', 'Dirección Demo', '999999999', 'A')");
  $conn->exec("INSERT IGNORE INTO ipa_stock (Ipa_stock_name, Ipa_stock_desc, Ipa_stock_onhand, Ipa_stock_price, Ipa_stock_status) VALUES ('Producto Demo', 'Descripción demo', 100, 25.00, 'activo')");

  $stmt = $conn->query("SELECT Ipa_stock_ID, Ipa_stock_name, Ipa_stock_price FROM ipa_stock WHERE Ipa_stock_name='Producto Demo' LIMIT 1");
  $p = $stmt->fetch(PDO::FETCH_ASSOC);

  $conn->prepare("INSERT INTO ipa_invoices (Ipa_inv_date, Ipa_inv_client_ID, Ipa_inv_client_name, Ipa_inv_client_address, Ipa_inv_amount, ipa_inv_status) VALUES (NOW(), 'DEMO', 'Cliente Demo', 'Dirección Demo', 50.00, 'pagada')")->execute();
  $invId = (int)$conn->lastInsertId();

  $conn->prepare("INSERT INTO ipa_invoice_items (Ipa_invitem_no, Ipa_invitem_inv_no, Ipa_invitem_stock_ID, Ipa_invitem_stock_name, Ipa_invitem_qty, Ipa_invitem_stock_price, Ipa_invitem_stock_amount, Ipa_inv_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'A')")->execute([
    sprintf('IT%06d', $invId * 1000 + 1),
    (string)$invId,
    (string)$p['Ipa_stock_ID'],
    $p['Ipa_stock_name'],
    '2',
    number_format((float)$p['Ipa_stock_price'], 2, '.', ''),
    number_format(2 * (float)$p['Ipa_stock_price'], 2, '.', '')
  ]);

  $conn->prepare("UPDATE ipa_stock SET Ipa_stock_onhand = Ipa_stock_onhand - 2 WHERE Ipa_stock_ID=?")->execute([$p['Ipa_stock_ID']]);

  $conn->commit();
  header('Location: invoices.php');
} catch (Exception $e) {
  $conn->rollBack();
  echo "Error demo: " . htmlspecialchars($e->getMessage());
}