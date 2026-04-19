<?php
require 'db.php';
require_login();

$page_title = 'Bildirişlər — TaskFlow';
$uid = $_SESSION['user_id'];

// Hamısını oxunmuş et
if (isset($_GET['mark_all'])) {
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$uid]);
    header("Location: notifications.php");
    exit;
}

// Tək bildirişi oxunmuş et
if (isset($_GET['read'])) {
    $nid = (int)$_GET['read'];
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?")->execute([$nid, $uid]);
    header("Location: notifications.php");
    exit;
}

// Bildirişi sil
if (isset($_GET['delete'])) {
    $nid = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?")->execute([$nid, $uid]);
    header("Location: notifications.php");
    exit;
}

$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$notifs->execute([$uid]);
$notifications = $notifs->fetchAll();

include 'header.php';
?>

<main class="container">
    <div class="page-title">
        <h2>🔔 Bildirişlər</h2>
        <?php if (!empty($notifications)): ?>
        <a href="?mark_all=1" class="btn btn-secondary2">✅ Hamısını oxunmuş et</a>
        <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="empty-state">
            <p>🔕 Heç bir bildiriş yoxdur.</p>
        </div>
    <?php else: ?>
    <div class="notif-list">
        <?php foreach ($notifications as $n): ?>
        <div class="notif-item <?= $n['is_read'] ? 'notif-read' : 'notif-unread' ?>">
            <div class="notif-content">
                <p class="notif-msg"><?= htmlspecialchars($n['message']) ?></p>
                <span class="notif-time">🕐 <?= date('d.m.Y H:i', strtotime($n['created_at'])) ?></span>
            </div>
            <div class="notif-actions">
                <?php if (!$n['is_read']): ?>
                <a href="?read=<?= $n['id'] ?>" class="btn btn-edit" title="Oxunmuş et">👁</a>
                <?php endif; ?>
                <a href="?delete=<?= $n['id'] ?>" class="btn btn-delete" 
                   onclick="return confirm('Silinsin?')" title="Sil">🗑</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

</body>
</html>
