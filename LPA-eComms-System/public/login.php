<?php
session_start();
require_once '../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Ajuste: nombre correcto de tabla y columnas
    $stmt = $conn->prepare("
        SELECT ipa_user_ID, ipa_user_username, ipa_user_password, ipa_inv_status
        FROM Ipa_users
        WHERE ipa_user_username = ?
        LIMIT 1
    ");
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    if ($row) {
        $estado = strtolower($row['ipa_inv_status']);
        if ($estado !== 'activo' && $estado !== 'enabled') {
            $error = 'Usuario deshabilitado.';
        } elseif (password_verify($password, $row['ipa_user_password'])) {
            $_SESSION['user'] = [
                'id' => $row['ipa_user_ID'],
                'username' => $row['ipa_user_username']
            ];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Credenciales inválidas.';
        }
    } else {
        $error = 'Usuario no encontrado.';
    }
}
?>

<?php include '../includes/header.php'; ?>
<div class="d-flex justify-content-center align-items-center" style="min-height:60vh;">
  <div class="card shadow" style="max-width: 360px; width: 100%;">
    <div class="card-header text-center">
      <strong>User Login</strong>
    </div>
    <div class="card-body">
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <form method="post" autocomplete="off">
        <div class="mb-3">
          <label class="form-label">User name</label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Login</button>
      </form>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>