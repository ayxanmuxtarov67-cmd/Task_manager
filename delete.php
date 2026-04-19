<?php
require 'db.php';
require_login();

$uid = $_SESSION['user_id'];
$id  = (int)($_GET['id'] ?? 0);

if ($id) {
    // Yalnız öz tapşırığını silə bilər
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $uid]);
}

header("Location: tasks.php");
exit;
