<?php
require_once '../config/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM ipa_stock WHERE Ipa_stock_ID = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($item ?: []);
}