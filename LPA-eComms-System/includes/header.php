<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>LPA eComms</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="bg-light">
<header class="bg-primary text-white py-3">
  <div class="container d-flex justify-content-between align-items-center">
    <h1 class="h4 mb-0">LPA eComms</h1>
    <?php if (!empty($_SESSION['user'])): ?>
      <nav class="d-none d-md-block">
        <a href="index.php" class="text-white me-3">Home</a>
        <a href="stock.php" class="text-white me-3">Stock</a>
        <a href="invoices.php" class="text-white me-3">Ventas & Facturas</a>
        <a href="logout.php" class="text-white">Salir</a>
      </nav>
      <div class="dropdown d-md-none">
        <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Menú</button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="index.php">Home</a></li>
          <li><a class="dropdown-item" href="stock.php">Stock</a></li>
          <li><a class="dropdown-item" href="invoices.php">Ventas & Facturas</a></li>
          <li><a class="dropdown-item" href="logout.php">Salir</a></li>
        </ul>
      </div>
    <?php endif; ?>
  </div>
</header>
<main class="container my-4">
