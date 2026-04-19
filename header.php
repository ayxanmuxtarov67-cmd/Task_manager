<?php
// Bu fayl hər səhifəyə include edilir
$notif_count = isset($pdo, $_SESSION['user_id']) ? unread_notifications($pdo, $_SESSION['user_id']) : 0;
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $page_title ?? 'Task Manager' ?></title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <a href="index.php" class="nav-brand">📋 TaskFlow</a>
        <div class="nav-links">
            <a href="index.php"        class="nav-link <?= $current_page==='index.php'?'active':'' ?>">🏠 Ana Səhifə</a>
            <a href="tasks.php"        class="nav-link <?= $current_page==='tasks.php'?'active':'' ?>">📝 Tapşırıqlar</a>
            <a href="analytics.php"    class="nav-link <?= $current_page==='analytics.php'?'active':'' ?>">📊 Analitika</a>
            <a href="notifications.php" class="nav-link notif-link <?= $current_page==='notifications.php'?'active':'' ?>">
                🔔 Bildirişlər
                <?php if ($notif_count > 0): ?>
                    <span class="notif-badge"><?= $notif_count ?></span>
                <?php endif; ?>
            </a>
        </div>
        <div class="nav-user">
            <span class="user-name">👤 <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span>
            <a href="logout.php" class="btn-logout">Çıxış</a>
        </div>
    </div>
</nav>
