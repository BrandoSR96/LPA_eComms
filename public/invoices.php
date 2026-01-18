<?php
// public/invoices.php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php'; // aquí tienes $conn (PDO)

$results = [];
$total = 0.00;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  
  if ($action === 'search') {
    $q = trim($_POST['query'] ?? '');
    $like = "%$q%";

    $stmt = $conn->prepare("
      SELECT ipa_inv_no, Ipa_inv_date, Ipa_inv_client_name, Ipa_inv_amount
      FROM ipa_invoices
      WHERE Ipa_inv_client_name LIKE ? OR DATE(Ipa_inv_date) LIKE ?
      ORDER BY Ipa_inv_date DESC
    ");
    $stmt->execute([$like, $like]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $r) {
      $total += (float)$r['Ipa_inv_amount'];
    }

  } elseif ($action === 'seed') {
    // Semilla rápida de demo
    $stmt = $conn->prepare("
      INSERT INTO ipa_invoices (Ipa_inv_date, Ipa_inv_client_ID, Ipa_inv_client_name, Ipa_inv_client_address, Ipa_inv_amount, ipa_inv_status)
      VALUES (NOW(), 'DEMO', 'Cliente Demo', 'Av. Siempre Viva 123', 150.50, 'pendiente')
    ");
    if ($stmt->execute()) {
      $message = 'Factura demo creada.';
    } else {
      $message = 'Error al crear factura demo.';
    }
  }
}

include '../includes/header.php';
?>
<div class="row">
  <div class="col-12">
    <div class="p-4 bg-white shadow-sm rounded">
      <h2 class="h5 mb-3">Ventas & Facturación</h2>

      <?php if (!empty($message)): ?>
        <div class="alert alert-info py-2"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>

      <form method="post" class="row g-3">
        <input type="hidden" name="action" value="search">
        <div class="col-md-8">
          <label class="form-label">Buscar factura</label>
          <input type="text" name="query" class="form-control" placeholder="Fecha (YYYY-MM-DD) o Cliente">
        </div>
        <div class="col-md-4 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary w-100">Buscar</button>
          <!--<button type="submit" name="action" value="seed" class="btn btn-outline-secondary">Crear demo</button>-->
        </div>
      </form>

      <div class="table-responsive mt-3">
        <table class="table table-striped table-sm">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Cliente</th>
              <th>Monto</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($results)): ?>
              <?php foreach ($results as $row): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['Ipa_inv_date']); ?></td>
                  <td><?php echo htmlspecialchars($row['Ipa_inv_client_name']); ?></td>
                  <td><?php echo number_format((float)$row['Ipa_inv_amount'], 2); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="3" class="text-center text-muted">Sin resultados</td></tr>
            <?php endif; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="2" class="text-end">Total:</th>
              <th id="totalAmount"><?php echo number_format($total, 2); ?></th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>