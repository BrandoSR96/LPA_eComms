<?php
// public/stock.php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php'; // aquí tienes $conn (PDO)

$message = '';
$searchResults = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  if ($action === 'save') {
    $stock_id = trim($_POST['Ipa_stock_ID'] ?? '');
    $name     = trim($_POST['Ipa_stock_name'] ?? '');
    $desc     = trim($_POST['Ipa_stock_desc'] ?? '');
    $onhand   = (int)($_POST['Ipa_stock_onhand'] ?? 0);
    $price    = (float)($_POST['Ipa_stock_price'] ?? 0);
    $status   = trim($_POST['Ipa_stock_status'] ?? 'activo');

    if ($name) {
        if ($stock_id) {
        // Update existente por ID
        $stmt = $conn->prepare("
            UPDATE ipa_stock
            SET Ipa_stock_name=?, Ipa_stock_desc=?, Ipa_stock_onhand=?, Ipa_stock_price=?, Ipa_stock_status=?
            WHERE Ipa_stock_ID=?
        ");
        $ok = $stmt->execute([$name, $desc, $onhand, $price, $status, $stock_id]);
        } else {
        // Insert nuevo
        $stmt = $conn->prepare("
            INSERT INTO ipa_stock (Ipa_stock_name, Ipa_stock_desc, Ipa_stock_onhand, Ipa_stock_price, Ipa_stock_status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $ok = $stmt->execute([$name, $desc, $onhand, $price, $status]);
        }
        $message = $ok ? 'Registro guardado correctamente.' : 'Error al guardar.';
    } else {
        $message = 'El nombre del producto es obligatorio.';
    }
    } elseif ($action === 'search') {
    $q = '%' . trim($_POST['query'] ?? '') . '%';
    $stmt = $conn->prepare("
      SELECT Ipa_stock_ID, Ipa_stock_name, Ipa_stock_desc, Ipa_stock_onhand, Ipa_stock_price, Ipa_stock_status
      FROM ipa_stock
      WHERE Ipa_stock_name LIKE ? OR Ipa_stock_desc LIKE ?
      ORDER BY Ipa_stock_name ASC
    ");
    $stmt->execute([$q, $q]);
    $searchResults = $stmt->fetchAll();
  }
}
include '../includes/header.php';
?>
<div class="row">
  <div class="col-12">
    <div class="p-4 bg-white shadow-sm rounded">
      <h2 class="h5 mb-3">Gestión de Stock</h2>

      <?php if (!empty($message)): ?>
        <div class="alert alert-info py-2"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>

      <form method="post" class="row g-3">
        <input type="hidden" name="action" value="save">
        <div class="col-md-4">
          <label class="form-label">Stock ID</label>
          <input type="number" name="Ipa_stock_ID" class="form-control" placeholder="(opcional para actualizar)">
        </div>
        <div class="col-md-8">
          <label class="form-label">Nombre del ítem</label>
          <input type="text" name="Ipa_stock_name" class="form-control" required>
        </div>
        <div class="col-12">
          <label class="form-label">Descripción</label>
          <input type="text" name="Ipa_stock_desc" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Cantidad disponible (on-hand)</label>
          <input type="number" name="Ipa_stock_onhand" class="form-control" min="0" value="0" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Precio</label>
          <input type="number" step="0.01" name="Ipa_stock_price" class="form-control" min="0" value="0.00" required>
        </div>
        <div class="col-md-4">
          <label class="form-label d-block">Estado</label>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="Ipa_stock_status" id="enabled" value="activo" checked>
            <label class="form-check-label" for="enabled">Habilitado</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="Ipa_tock_status" id="disabled" value="inactivo">
            <label class="form-check-label" for="disabled">Deshabilitado</label>
          </div>
        </div>
        <div class="col-12 d-flex gap-2">
          <button type="submit" class="btn btn-success">Guardar</button>
          <button type="button" class="btn btn-secondary" onclick="document.getElementById('searchForm').scrollIntoView()">Buscar</button>
          <a href="index.php" class="btn btn-outline-dark">Cerrar</a>
        </div>
      </form>

      <hr class="my-4">

      <form id="searchForm" method="post" class="row g-3">
        <input type="hidden" name="action" value="search">
        <div class="col-md-8">
          <label class="form-label">Buscar ítem</label>
          <input type="text" name="query" class="form-control" placeholder="Nombre o descripción">
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100">Buscar</button>
        </div>
      </form>

      <?php if (!empty($searchResults)): ?>
        <div class="table-responsive mt-3">
          <table class="table table-striped table-sm">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>On-hand</th>
                <th>Precio</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($searchResults as $row): ?>
                <tr>
                  <td><?php echo (int)$row['Ipa_stock_ID']; ?></td>
                  <td><?php echo htmlspecialchars($row['Ipa_stock_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['Ipa_stock_desc']); ?></td>
                  <td><?php echo (int)$row['Ipa_stock_onhand']; ?></td>
                  <td><?php echo number_format((float)$row['Ipa_stock_price'], 2); ?></td>
                  <td><span class="badge bg-<?php echo strtolower($row['Ipa_stock_status'])==='activo'?'success':'secondary'; ?>">
                    <?php echo htmlspecialchars($row['Ipa_stock_status']); ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>