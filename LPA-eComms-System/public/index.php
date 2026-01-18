<?php
// public/index.php
require_once '../includes/auth.php';
require_login();
include '../includes/header.php';
?>
<div class="row">
  <div class="col-12">
    <div class="p-4 bg-white shadow-sm rounded">
      <h2 class="h5 mb-3">Bienvenido, <?php echo htmlspecialchars($_SESSION['user']['username']); ?></h2>
      <p>Usa el menú para navegar: gestión de stock y ventas/facturación.</p>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>