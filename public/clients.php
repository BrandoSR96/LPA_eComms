<?php
// public/clients.php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = trim($_POST['Ipa_client_ID'] ?? '');
  $fn = trim($_POST['Ipa_client_firstname'] ?? '');
  $ln = trim($_POST['Ipa_client_lastname'] ?? '');
  $addr = trim($_POST['Ipa_client_address'] ?? '');
  $phone = trim($_POST['Ipa_client_phone'] ?? '');
  $status = trim($_POST['Ipa_client_status'] ?? 'A');

  if ($id && $fn) {
    $stmt = $conn->prepare("SELECT 1 FROM ipa_clients WHERE Ipa_client_ID=?");
    $stmt->execute([$id]);
    if ($stmt->fetch()) {
      $stmt = $conn->prepare("UPDATE ipa_clients SET Ipa_client_firstname=?, Ipa_client_lastname=?, Ipa_client_address=?, Ipa_client_phone=?, Ipa_client_status=? WHERE Ipa_client_ID=?");
      $ok = $stmt->execute([$fn, $ln, $addr, $phone, $status, $id]);
    } else {
      $stmt = $conn->prepare("INSERT INTO ipa_clients (Ipa_client_ID, Ipa_client_firstname, Ipa_client_lastname, Ipa_client_address, Ipa_client_phone, Ipa_client_status) VALUES (?, ?, ?, ?, ?, ?)");
      $ok = $stmt->execute([$id, $fn, $ln, $addr, $phone, $status]);
    }
    $message = $ok ? 'Cliente guardado correctamente.' : 'Error al guardar cliente.';
  } else {
    $message = 'ID y nombre son obligatorios.';
  }
}
include '../includes/header.php';
?>
<h2 class="h5 mb-3">Gestión de Clientes</h2>
<?php if (!empty($message)): ?>
  <div class="alert alert-info py-2"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>
<form method="post" class="row g-3">
  <div class="col-md-4">
    <label class="form-label">ID (DNI/RUC)</label>
    <input type="text" name="Ipa_client_ID" class="form-control" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">Nombres</label>
    <input type="text" name="Ipa_client_firstname" class="form-control" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">Apellidos</label>
    <input type="text" name="Ipa_client_lastname" class="form-control">
  </div>
  <div class="col-md-6">
    <label class="form-label">Dirección</label>
    <input type="text" name="Ipa_client_address" class="form-control">
  </div>
  <div class="col-md-3">
    <label class="form-label">Teléfono</label>
    <input type="text" name="Ipa_client_phone" class="form-control">
  </div>
  <div class="col-md-3">
    <label class="form-label">Estado</label>
    <select name="Ipa_client_status" class="form-select">
      <option value="A">Activo</option>
      <option value="I">Inactivo</option>
    </select>
  </div>
  <div class="col-12">
    <button type="submit" class="btn btn-success">Guardar cliente</button>
  </div>
</form>
<?php include '../includes/footer.php'; ?>