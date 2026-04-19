-- =============================================
-- TAM VERSİYA - VERİLƏNLƏR BAZASI QURULUMU
-- =============================================

CREATE DATABASE IF NOT EXISTS task_manager_full
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE task_manager_full;

-- İstifadəçilər
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tapşırıqlar
CREATE TABLE IF NOT EXISTS tasks (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    title       VARCHAR(255) NOT NULL,
    description TEXT,
    status      ENUM('todo','inprogress','done') DEFAULT 'todo',
    priority    ENUM('low','medium','high')      DEFAULT 'medium',
    deadline    DATE,
    label       VARCHAR(100),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tapşırıq paylaşımları
CREATE TABLE IF NOT EXISTS task_shares (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    task_id            INT NOT NULL,
    shared_with_user_id INT NOT NULL,
    shared_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_share (task_id, shared_with_user_id),
    FOREIGN KEY (task_id)             REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (shared_with_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bildirişlər
CREATE TABLE IF NOT EXISTS notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    message    VARCHAR(255) NOT NULL,
    link       VARCHAR(255),
    is_read    TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Nümunə istifadəçi (şifrə: 12345)
INSERT INTO users (name, email, password) VALUES
('Əli Əliyev', 'ali@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Qeyd: nümunə şifrə hash-i "password" üçündür. Öz istifadəçini qeydiyyatdan keçir.
