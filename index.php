<?php
require 'db.php';
require_login();

$page_title = 'Dashboard — TaskFlow';
$uid = $_SESSION['user_id'];

// Statistika
$stats_q = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(status='todo') as todo,
        SUM(status='inprogress') as inprogress,
        SUM(status='done') as done,
        SUM(priority='high' AND status!='done') as urgent
    FROM tasks WHERE user_id = ?
");
$stats_q->execute([$uid]);
$stats = $stats_q->fetch();

// Deadline yaxınlaşan tapşırıqlar (3 gün ərzində)
$upcoming = $pdo->prepare("
    SELECT * FROM tasks 
    WHERE user_id = ? AND status != 'done' AND deadline IS NOT NULL 
    AND deadline BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
    ORDER BY deadline ASC LIMIT 5
");
$upcoming->execute([$uid]);
$upcoming_tasks = $upcoming->fetchAll();

// Son əlavə edilən tapşırıqlar
$recent = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$recent->execute([$uid]);
$recent_tasks = $recent->fetchAll();

// Paylaşılan tapşırıqlar
$shared = $pdo->prepare("
    SELECT t.*, u.name as owner_name FROM tasks t
    JOIN task_shares ts ON ts.task_id = t.id
    JOIN users u ON u.id = t.user_id
    WHERE ts.shared_with_user_id = ?
    ORDER BY t.created_at DESC LIMIT 5
");
$shared->execute([$uid]);
$shared_tasks = $shared->fetchAll();

include 'header.php';
?>

<main class="container">
    <div class="page-title">
        <h2>👋 Salam, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h2>
        <a href="add_task.php" class="btn btn-primary">+ Yeni Tapşırıq</a>
    </div>

    <!-- Statistika -->
    <div class="stats-row">
        <div class="stat-card stat-total">
            <span class="stat-num"><?= $stats['total'] ?></span>
            <span class="stat-label">Cəmi</span>
        </div>
        <div class="stat-card stat-todo">
            <span class="stat-num"><?= $stats['todo'] ?></span>
            <span class="stat-label">Gözləyir</span>
        </div>
        <div class="stat-card stat-inprogress">
            <span class="stat-num"><?= $stats['inprogress'] ?></span>
            <span class="stat-label">Davam edir</span>
        </div>
        <div class="stat-card stat-done">
            <span class="stat-num"><?= $stats['done'] ?></span>
            <span class="stat-label">Tamamlandı</span>
        </div>
        <div class="stat-card stat-urgent">
            <span class="stat-num"><?= $stats['urgent'] ?></span>
            <span class="stat-label">🔴 Təcili</span>
        </div>
    </div>

    <div class="dashboard-grid">

        <!-- Deadline yaxınlaşır -->
        <?php if (!empty($upcoming_tasks)): ?>
        <div class="dash-section">
            <h3>⚠️ Deadline Yaxınlaşır (3 gün)</h3>
            <div class="mini-task-list">
                <?php foreach ($upcoming_tasks as $t): ?>
                <div class="mini-task mini-urgent">
                    <div>
                        <strong><?= htmlspecialchars($t['title']) ?></strong>
                        <span class="mini-date">📅 <?= date('d.m.Y', strtotime($t['deadline'])) ?></span>
                    </div>
                    <a href="edit_task.php?id=<?= $t['id'] ?>" class="btn btn-edit">Düzəlt</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Son tapşırıqlar -->
        <div class="dash-section">
            <h3>🕐 Son Tapşırıqlar</h3>
            <?php if (empty($recent_tasks)): ?>
                <p class="no-data">Hələ tapşırıq yoxdur. <a href="add_task.php">Əlavə et →</a></p>
            <?php else: ?>
            <div class="mini-task-list">
                <?php foreach ($recent_tasks as $t):
                    $s_map = ['todo'=>'⏳','inprogress'=>'🔄','done'=>'✅'];
                ?>
                <div class="mini-task">
                    <div>
                        <strong><?= htmlspecialchars($t['title']) ?></strong>
                        <span class="mini-status"><?= $s_map[$t['status']] ?></span>
                    </div>
                    <a href="edit_task.php?id=<?= $t['id'] ?>" class="btn btn-edit">Düzəlt</a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <a href="tasks.php" class="view-all">Hamısına bax →</a>
        </div>

        <!-- Paylaşılan tapşırıqlar -->
        <div class="dash-section">
            <h3>🤝 Paylaşılan Tapşırıqlar</h3>
            <?php if (empty($shared_tasks)): ?>
                <p class="no-data">Heç bir tapşırıq paylaşılmayıb.</p>
            <?php else: ?>
            <div class="mini-task-list">
                <?php foreach ($shared_tasks as $t): ?>
                <div class="mini-task">
                    <div>
                        <strong><?= htmlspecialchars($t['title']) ?></strong>
                        <span class="mini-owner">👤 <?= htmlspecialchars($t['owner_name']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>
</main>

</body>
</html>
