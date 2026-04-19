<?php
require 'db.php';

if (!empty($_SESSION['user_id'])) {
    header("Location: index.php"); exit;
}

$errors = [];
$data   = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['name']  = trim($_POST['name'] ?? '');
    $data['email'] = trim($_POST['email'] ?? '');
    $password      = $_POST['password'] ?? '';
    $password2     = $_POST['password2'] ?? '';

    if (empty($data['name']))              $errors[] = "Ad boş ola bilməz!";
    if (empty($data['email']))             $errors[] = "Email boş ola bilməz!";
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Email formatı yanlışdır!";
    if (strlen($password) < 6)             $errors[] = "Şifrə ən az 6 simvol olmalıdır!";
    if ($password !== $password2)          $errors[] = "Şifrələr eyni deyil!";

    if (empty($errors)) {
        // Email mövcuddurmu?
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$data['email']]);
        if ($check->fetch()) {
            $errors[] = "Bu email artıq qeydiyyatdan keçib!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?,?,?)");
            $stmt->execute([$data['name'], $data['email'], $hash]);

            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id']   = $user_id;
            $_SESSION['user_name'] = $data['name'];

            // Xoş gəldin bildirişi
            create_notification($pdo, $user_id, "TaskFlow-a xoş gəldiniz, {$data['name']}! 🎉");

            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Qeydiyyat — TaskFlow</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">📋</div>
        <h1 class="auth-title">Qeydiyyat</h1>
        <p class="auth-subtitle">Yeni hesab yaradın</p>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?>
                    <p>❌ <?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Ad Soyad</label>
                <input type="text" name="name" placeholder="Əli Əliyev" 
                       value="<?= htmlspecialchars($data['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="email@mail.com" 
                       value="<?= htmlspecialchars($data['email']) ?>" required>
            </div>
            <div class="form-group">
                <label>Şifrə (min. 6 simvol)</label>
                <input type="password" name="password" placeholder="Şifrənizi daxil edin" required>
            </div>
            <div class="form-group">
                <label>Şifrəni təsdiq edin</label>
                <input type="password" name="password2" placeholder="Şifrəni yenidən daxil edin" required>
            </div>
            <button type="submit" class="btn-auth">Qeydiyyatdan Keç</button>
        </form>

        <p class="auth-switch">Hesabınız var? <a href="login.php">Daxil olun</a></p>
    </div>
</div>

</body>
</html>
