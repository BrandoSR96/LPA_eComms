<?php
// public/invoice_detail.php?inv=123
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';

$inv = (int)($_GET['inv'] ?? 0);

// Obtener cabecera de la factura
$stmt = $conn->prepare("SELECT * FROM ipa_invoices WHERE ipa_inv_no=?");
$stmt->execute([$inv]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener detalle de la factura
$stmt = $conn->prepare("
  SELECT Ipa_invitem_stock_name, Ipa_invitem_qty, Ipa_invitem_stock_price, Ipa_invitem_stock_amount
  FROM ipa_invoice_items
  WHERE Ipa_invitem_inv_no=?
");
$stmt->execute([(string)$inv]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>
<h2 class="h5 mb-3">Detalle de Factura #<?php echo $inv; ?></h2>

<?php if ($invoice): ?>
  <p><strong>Fecha:</strong> <?php echo htmlspecialchars($invoice['Ipa_inv_date']); ?></p>
  <p><strong>Cliente:</strong> <?php echo htmlspecialchars($invoice['Ipa_inv_client_name']); ?></p>
  <p><strong>Dirección:</strong> <?php echo htmlspecialchars($invoice['Ipa_inv_client_address']); ?></p>
  <p><strong>Monto total:</strong> S/. <?php echo number_format((float)$invoice['Ipa_inv_amount'], 2); ?></p>

  <table class="table table-bordered table-sm mt-3">
    <thead>
      <tr>
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Precio unitario</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($items)): ?>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?php echo htmlspecialchars($it['Ipa_invitem_stock_name']); ?></td>
            <td><?php echo (int)$it['Ipa_invitem_qty']; ?></td>
            <td><?php echo number_format((float)$it['Ipa_invitem_stock_price'], 2); ?></td>
            <td><?php echo number_format((float)$it['Ipa_invitem_stock_amount'], 2); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4" class="text-center text-muted">No hay productos registrados en esta factura.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
<?php else: ?>
  <div class="alert alert-warning">Factura no encontrada.</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>