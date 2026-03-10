<?php
session_start();

$host = 'localhost';
$dbname = 'extreme_ethics';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Load settings from database
$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Define constants for easy access
define('ADMIN_EMAIL', $settings['admin_email'] ?? 'admin@extremeethics.co.za');
define('ADMIN_WHATSAPP', $settings['admin_whatsapp'] ?? '27692070042');

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
?>