<?php
require_once '../config/config.php';
require_once '../config/database.php';

$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$new_password, $email]);

    $success = "Password updated. You can now login.";
    redirect('login.php');
}
?>
<!-- Password reset form here -->
