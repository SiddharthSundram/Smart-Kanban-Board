<?php
require_once '../config/config.php';

$token = $_GET['token'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Validate token
    $stmt = $pdo->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if ($reset) {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$password, $reset['user_id']]);

        $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$reset['user_id']]);

        echo "Password updated successfully.";
    } else {
        echo "Invalid or expired token.";
    }
}
?>

<!-- HTML Form -->
<form method="POST">
  <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>" />
  <input type="password" name="password" required placeholder="New Password" />
  <button type="submit">Reset Password</button>
</form>
