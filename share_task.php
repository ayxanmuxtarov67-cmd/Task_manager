<?php
require 'db.php';
require_login();

$page_title = 'Tapşırığı Paylaş — TaskFlow';
$uid = $_SESSION['user_id'];
$id  = (int)($_GET['id'] ?? 0);

if (!$id) { header("Location: tasks.php"); exit; }

// Tapşırıq mövcuddurmu və sahibi bu istifadəçidirmi?
$task_q = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$task_q->execute([$id, $uid]);
$task = $task_q->fetch();
if (!$task) { header("Location: tasks.php"); exit; }

$success = '';
$error   = '';

// Paylaş
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $error = "Email daxil edin!";
    } else {
        $user_q = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $user_q->execute([$email]);
        $target_user = $user_q->fetch();

        if (!$target_user) {
            $error = "Bu email ilə istifadəçi tapılmadı!";
        } elseif ($target_user['id'] === $uid) {
            $error = "Öz tapşırığınızı özünüzə paylaşa bilməzsiniz!";
        } else {
            // Artıq paylaşılıbmı?
            $check = $pdo->prepare("SELECT id FROM task_shares WHERE task_id = ? AND shared_with_user_id = ?");
            $check->execute([$id, $target_user['id']]);
            if ($check->fetch()) {
                $error = "Bu tapşırıq artıq həmin istifadəçiyə paylaşılıb!";
            } else {
                $pdo->prepare("INSERT INTO task_shares (task_id, shared_with_user_id) VALUES (?,?)")
                    ->execute([$id, $target_user['id']]);

                // Hədəf istifadəçiyə bildiriş
                create_notification(
                    $pdo,
                    $target_user['id'],
                    "{$_SESSION['user_name']} sizinlə tapşırıq paylaşdı: \"{$task['title']}\"",
                    "index.php"
                );
                $success = "Tapşırıq {$target_user['name']}-ə paylaşıldı!";
            }
        }
    }
}

// Mövcud paylaşımlar
$shares_q = $pdo->prepare("
    SELECT u.name, u.email, ts.shared_at, ts.id as share_id
    FROM task_shares ts
    JOIN users u ON u.id = ts.shared_with_user_id
    WHERE ts.task_id = ?
");
$shares_q->execute([$id]);
$shares = $shares_q->fetchAll();

include 'header.php';
?>

<main class="container">
    <div class="form-card">
        <h2>🤝 Tapşırığı Paylaş</h2>
        <p class="share-task-name">📌 <strong><?= htmlspecialchars($task['title']) ?></strong></p>

        <?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>İstifadəçinin Email-i</label>
                <input type="email" name="email" placeholder="dost@mail.com" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">📤 Paylaş</button>
                <a href="tasks.php" class="btn btn-cancel">← Geri</a>
            </div>
        </form>

        <?php if (!empty($shares)): ?>
        <div class="share-list">
            <h3>Paylaşılan istifadəçilər</h3>
            <?php foreach ($shares as $s): ?>
            <div class="share-item">
                <div>
                    <strong><?= htmlspecialchars($s['name']) ?></strong>
                    <span class="share-email"><?= htmlspecialchars($s['email']) ?></span>
                </div>
                <span class="share-date"><?= date('d.m.Y', strtotime($s['shared_at'])) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
