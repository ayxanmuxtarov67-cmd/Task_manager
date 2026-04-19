<?php
require 'db.php';

if (!empty($_SESSION['user_id'])) {
    header("Location: index.php"); exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Email və şifrə daxil edin!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Email və ya şifrə yanlışdır!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daxil Ol — TaskFlow</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">📋</div>
        <h1 class="auth-title">TaskFlow</h1>
        <p class="auth-subtitle">Tapşırıqlarınızı idarə edin</p>

        <?php if ($error): ?>
            <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="email@mail.com" 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Şifrə</label>
                <input type="password" name="password" placeholder="Şifrənizi daxil edin" required>
            </div>
            <button type="submit" class="btn-auth">Daxil Ol</button>
        </form>

        <p class="auth-switch">Hesabınız yoxdur? <a href="register.php">Qeydiyyatdan keçin</a></p>
    </div>
</div>

</body>
</html>
