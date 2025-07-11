<?php
require_once '../config/config.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['reset_error'] = "No user found with that email.";
        header("Location: forgot_password.php");
        exit;
    }

    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', time() + 3600); 

    $stmt = $pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $token, $expires_at]);

    $resetLink = "http://localhost/smart-kanban-board/auth/reset_password.php?token=$token";

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  
        $mail->SMTPAuth = true;
        $mail->Username = 'your@gmail.com';  
        $mail->Password = 'your_app_password'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('no-reply@smartkanban.com', 'Smart Kanban Board');
        $mail->addAddress($user['email'], $user['full_name']);

        $mail->isHTML(true);
        $mail->Subject = 'Reset your password';
        $mail->Body = "Hi {$user['full_name']},<br><br>
        Click the link below to reset your password:<br><br>
        <a href='$resetLink'>$resetLink</a><br><br>
        This link expires in 1 hour.";

        $mail->send();

        $_SESSION['reset_success'] = "Reset link sent to your email.";
        header("Location: forgot_password.php");
    } catch (Exception $e) {
        $_SESSION['reset_error'] = "Email could not be sent: " . $mail->ErrorInfo;
        header("Location: forgot_password.php");
    }
}
