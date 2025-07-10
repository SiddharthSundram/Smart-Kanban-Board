<?php
require_once '../config/config.php';
session_start();

$token = $_POST['token'];
$password = $_POST['password'];
$confirm = $_POST['confirm'];

if (!$token || $password !== $confirm) {
    die("Password mismatch or invalid request.");
}

$stmt = $pdo->prepare("SELECT * FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$tokenData = $stmt->fetch();

if (!$tokenData) {
    die("Token expired or invalid.");
}

// Update password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->execute([$hashedPassword, $tokenData['user_id']]);

// Delete used token
$stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
$stmt->execute([$token]);

echo "✅ Password has been reset. <a href='login.php'>Login now</a>";
