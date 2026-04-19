<?php
// Verilənlər bazası ayarları
define('DB_HOST', 'localhost');
define('DB_NAME', 'task_manager_full');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div style='font-family:sans-serif;padding:30px;color:#c0392b;background:#fde8e8;border-radius:8px;margin:20px;'>
        <h2>⚠️ Verilənlər bazası bağlantısı alınmadı</h2>
        <p>" . $e->getMessage() . "</p>
        <p>XAMPP işləyirmi? MySQL aktiv edilib?</p>
    </div>");
}

session_start();

/**
 * İstifadəçi daxil olmayıbsa login-ə yönləndir
 */
function require_login() {
    if (empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Bildiriş yarat
 */
function create_notification(PDO $pdo, int $user_id, string $message, string $link = ''): void {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?,?,?)");
    $stmt->execute([$user_id, $message, $link]);
}

/**
 * Oxunmamış bildiriş sayı
 */
function unread_notifications(PDO $pdo, int $user_id): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return (int) $stmt->fetchColumn();
}
