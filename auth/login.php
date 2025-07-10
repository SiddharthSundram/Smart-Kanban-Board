<?php
require_once '../config/config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        header("Location: ../dashboard/index.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!-- Simple login form using Tailwind CSS -->
<!-- Simple login form using Tailwind CSS -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <form method="post" class="bg-white p-6 rounded shadow-md w-96">
    <h2 class="text-xl font-bold mb-4">Login</h2>

    <?php if (isset($error)): ?>
      <p class="text-red-600 mb-2"><?= $error ?></p>
    <?php endif; ?>

    <input type="email" name="email" placeholder="Email" class="w-full p-2 mb-3 border rounded" required>
    <input type="password" name="password" placeholder="Password" class="w-full p-2 mb-1 border rounded" required>

    <!-- 🔑 Forgot password link -->
    <div class="text-right mb-3">
      <a href="forgot_password.php" class="text-sm text-blue-600 hover:underline">Forgot Password?</a>
    </div>

    <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded">Login</button>

    <p class="mt-2 text-sm">Don't have an account? 
      <a href="register.php" class="text-blue-500 hover:underline">Register</a>
    </p>
  </form>
</body>
</html>
