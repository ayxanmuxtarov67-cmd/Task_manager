<?php
require 'db.php';
require_login();

$page_title = 'Yeni Tapşırıq — TaskFlow';
$uid = $_SESSION['user_id'];
$errors = [];
$data = ['title'=>'','description'=>'','status'=>'todo','priority'=>'medium','deadline'=>'','label'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['title']       = trim($_POST['title'] ?? '');
    $data['description'] = trim($_POST['description'] ?? '');
    $data['status']      = $_POST['status'] ?? 'todo';
    $data['priority']    = $_POST['priority'] ?? 'medium';
    $data['deadline']    = $_POST['deadline'] ?? '';
    $data['label']       = trim($_POST['label'] ?? '');

    if (empty($data['title'])) $errors[] = "Tapşırıq adı boş ola bilməz!";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, status, priority, deadline, label) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$uid, $data['title'], $data['description'], $data['status'], $data['priority'], $data['deadline']?:null, $data['label']?:null]);

        // Bildiriş yarat
        create_notification($pdo, $uid, "Yeni tapşırıq əlavə edildi: \"{$data['title']}\"", "tasks.php");

        header("Location: tasks.php");
        exit;
    }
}

include 'header.php';
?>

<main class="container">
    <div class="form-card">
        <h2>➕ Yeni Tapşırıq</h2>

        <?php if ($errors): ?>
            <div class="alert alert-error"><?php foreach ($errors as $e): ?><p>❌ <?= htmlspecialchars($e) ?></p><?php endforeach; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Tapşırıq adı *</label>
                <input type="text" name="title" value="<?= htmlspecialchars($data['title']) ?>" placeholder="Tapşırığın adını yazın..." required>
            </div>
            <div class="form-group">
                <label>Açıqlama</label>
                <textarea name="description" rows="4" placeholder="Ətraflı açıqlama..."><?= htmlspecialchars($data['description']) ?></textarea>
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
                    <label>Son tarix (Deadline)</label>
                    <input type="date" name="deadline" value="<?= $data['deadline'] ?>" min="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Etiket</label>
                    <input type="text" name="label" value="<?= htmlspecialchars($data['label']) ?>" placeholder="Məs: Dərs, İş, Şəxsi...">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">✅ Əlavə Et</button>
                <a href="tasks.php" class="btn btn-cancel">İptal</a>
            </div>
        </form>
    </div>
</main>

</body>
</html>
