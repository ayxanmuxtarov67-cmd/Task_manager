<?php
require 'db.php';
require_login();

$page_title = 'Tapşırıqlar — TaskFlow';
$uid = $_SESSION['user_id'];

$status_filter   = $_GET['status']   ?? 'all';
$priority_filter = $_GET['priority'] ?? 'all';
$search          = $_GET['search']   ?? '';

$where  = ["t.user_id = ?"];
$params = [$uid];

if ($status_filter   !== 'all') { $where[] = "t.status = ?";   $params[] = $status_filter; }
if ($priority_filter !== 'all') { $where[] = "t.priority = ?"; $params[] = $priority_filter; }
if (!empty($search)) {
    $where[]  = "(t.title LIKE ? OR t.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql = "SELECT t.* FROM tasks t WHERE " . implode(" AND ", $where) . "
        ORDER BY 
            CASE t.priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END,
            t.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

include 'header.php';
?>

<main class="container">
    <div class="page-title">
        <h2>📝 Bütün Tapşırıqlar</h2>
        <a href="add_task.php" class="btn btn-primary">+ Yeni Tapşırıq</a>
    </div>

    <!-- Filtrlər -->
    <div class="filter-bar">
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="🔍 Axtar..." value="<?= htmlspecialchars($search) ?>">
            <input type="hidden" name="status"   value="<?= $status_filter ?>">
            <input type="hidden" name="priority" value="<?= $priority_filter ?>">
            <button type="submit" class="btn btn-secondary2">Axtar</button>
        </form>

        <div class="filter-group">
            <span class="filter-label">Status:</span>
            <?php foreach (['all'=>'Hamısı','todo'=>'⏳ Gözləyir','inprogress'=>'🔄 Davam','done'=>'✅ Bitdi'] as $v=>$l): ?>
            <a href="?status=<?=$v?>&priority=<?=$priority_filter?>&search=<?=urlencode($search)?>"
               class="tab <?= $status_filter===$v?'active':'' ?>"><?=$l?></a>
            <?php endforeach; ?>
        </div>

        <div class="filter-group">
            <span class="filter-label">Prioritet:</span>
            <?php foreach (['all'=>'Hamısı','high'=>'🔴 Yüksək','medium'=>'🟡 Orta','low'=>'🟢 Aşağı'] as $v=>$l): ?>
            <a href="?priority=<?=$v?>&status=<?=$status_filter?>&search=<?=urlencode($search)?>"
               class="tab <?= $priority_filter===$v?'active':'' ?>"><?=$l?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tapşırıqlar -->
    <?php if (empty($tasks)): ?>
        <div class="empty-state">
            <p>😕 Tapşırıq tapılmadı.</p>
            <a href="add_task.php" class="btn btn-primary">+ Yeni Tapşırıq əlavə et</a>
        </div>
    <?php else: ?>
    <div class="task-grid">
        <?php foreach ($tasks as $task):
            $priority_map = ['high'=>['🔴','Yüksək','priority-high'],'medium'=>['🟡','Orta','priority-medium'],'low'=>['🟢','Aşağı','priority-low']];
            $status_map   = ['todo'=>['⏳','Gözləyir','status-todo'],'inprogress'=>['🔄','Davam edir','status-inprogress'],'done'=>['✅','Tamamlandı','status-done']];
            [$p_icon,$p_label,$p_class] = $priority_map[$task['priority']];
            [$s_icon,$s_label,$s_class] = $status_map[$task['status']];
            $is_overdue = $task['deadline'] && $task['status']!=='done' && strtotime($task['deadline']) < time();
        ?>
        <div class="task-card <?= $task['status']==='done'?'task-done':'' ?> <?= $is_overdue?'task-overdue':'' ?>">
            <div class="task-card-header">
                <span class="badge <?= $p_class ?>"><?=$p_icon?> <?=$p_label?></span>
                <span class="badge <?= $s_class ?>"><?=$s_icon?> <?=$s_label?></span>
            </div>
            <h3 class="task-title"><?= htmlspecialchars($task['title']) ?></h3>
            <?php if ($task['description']): ?>
                <p class="task-desc"><?= htmlspecialchars(mb_substr($task['description'],0,80)) ?><?= mb_strlen($task['description'])>80?'...':'' ?></p>
            <?php endif; ?>
            <div class="task-meta">
                <?php if ($task['label']): ?><span class="label-tag">🏷 <?= htmlspecialchars($task['label']) ?></span><?php endif; ?>
                <?php if ($task['deadline']): ?><span class="deadline <?=$is_overdue?'overdue':''?>">📅 <?= date('d.m.Y', strtotime($task['deadline'])) ?><?= $is_overdue?' ⚠️':'' ?></span><?php endif; ?>
            </div>
            <div class="task-actions">
                <a href="edit_task.php?id=<?=$task['id']?>" class="btn btn-edit">✏️ Düzəlt</a>
                <a href="share_task.php?id=<?=$task['id']?>" class="btn btn-share">🤝 Paylaş</a>
                <a href="delete.php?id=<?=$task['id']?>" class="btn btn-delete"
                   onclick="return confirm('Silmək istəyirsiniz?')">🗑</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

</body>
</html>
