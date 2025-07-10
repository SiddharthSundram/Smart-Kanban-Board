<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
  <title>Forgot Password</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <form method="post" action="send_reset_link.php" class="bg-white p-6 rounded shadow w-96">
    <h2 class="text-xl font-bold mb-4">Forgot Password</h2>

    <?php if (isset($_SESSION['reset_error'])): ?>
      <p class="text-red-600"><?= $_SESSION['reset_error'] ?></p>
      <?php unset($_SESSION['reset_error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['reset_success'])): ?>
      <p class="text-green-600"><?= $_SESSION['reset_success'] ?></p>
      <?php unset($_SESSION['reset_success']); ?>
    <?php endif; ?>

    <input type="email" name="email" placeholder="Enter your email" required class="w-full border p-2 rounded mb-4" />
    <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded">Send Reset Link</button>
    <p class="mt-4 text-sm"><a href="login.php" class="text-blue-600 hover:underline">← Back to Login</a></p>
  </form>
</body>
</html>
