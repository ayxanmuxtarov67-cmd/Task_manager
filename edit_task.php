<?php
require 'db.php';
require_login();

$page_title = 'Tapşırığı Düzəlt — TaskFlow';
$uid = $_SESSION['user_id'];
$id  = (int)($_GET['id'] ?? 0);

if (!$id) { header("Location: tasks.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $uid]);
$data = $stmt->fetch();
if (!$data) { header("Location: tasks.php"); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['title']       = trim($_POST['title'] ?? '');
    $data['description'] = trim($_POST['description'] ?? '');
    $data['status']      = $_POST['status'] ?? 'todo';
    $data['priority']    = $_POST['priority'] ?? 'medium';
    $data['deadline']    = $_POST['deadline'] ?? '';
    $data['label']       = trim($_POST['label'] ?? '');

    if (empty($data['title'])) $errors[] = "Tapşırıq adı boş ola bilməz!";

    if (empty($errors)) {
        $upd = $pdo->prepare("UPDATE tasks SET title=?, description=?, status=?, priority=?, deadline=?, label=? WHERE id=? AND user_id=?");
        $upd->execute([$data['title'], $data['description'], $data['status'], $data['priority'], $data['deadline']?:null, $data['label']?:null, $id, $uid]);
        header("Location: tasks.php");
        exit;
    }
}

include 'header.php';
?>

<main class="container">
    <div class="form-card">
        <h2>✏️ Tapşırığı Düzəlt</h2>

        <?php if ($errors): ?>
            <div class="alert alert-error"><?php foreach ($errors as $e): ?><p>❌ <?= htmlspecialchars($e) ?></p><?php endforeach; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Tapşırıq adı *</label>
                <input type="text" name="title" value="<?= htmlspecialchars($data['title']) ?>" required>
            </div>
            <div class="form-group">
                <label>Açıqlama</label>
                <textarea name="description" rows="4"><?= htmlspecialchars($data['description']) ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="todo"       <?= $data['status']==='todo'      ?'selected':''?>>⏳ Gözləyir</option>
                        <option value="inprogress" <?= $data['status']==='inprogress'?'selected':''?>>🔄 Davam edir</option>
                        <option value="done"       <?= $data['status']==='done'      ?'selected':''?>>✅ Tamamlandı</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Prioritet</label>
                    <select name="priority">
                        <option value="low"    <?= $data['priority']==='low'   ?'selected':''?>>🟢 Aşağı</option>
                        <option value="medium" <?= $data['priority']==='medium'?'selected':''?>>🟡 Orta</option>
                        <option value="high"   <?= $data['priority']==='high'  ?'selected':''?>>🔴 Yüksək</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Son tarix</label>
                    <input type="date" name="deadline" value="<?= $data['deadline'] ?>">
                </div>
                <div class="form-group">
                    <label>Etiket</label>
                    <input type="text" name="label" value="<?= htmlspecialchars($data['label'] ?? '') ?>">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Yadda Saxla</button>
                <a href="tasks.php" class="btn btn-cancel">İptal</a>
            </div>
        </form>
    </div>
</main>

</body>
</html>
