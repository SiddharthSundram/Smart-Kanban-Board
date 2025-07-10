<?php
require_once '../config/config.php';

$board_id = $_POST['board_id'] ?? null;
$email = trim($_POST['email'] ?? '');
$role = $_POST['role'] ?? 'viewer';

function showError($message, $board_id) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Error - Share Board</title>
        <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
    </head>
    <body class='bg-gradient-to-br from-red-50 to-red-100 min-h-screen flex items-center justify-center px-4'>

        <div class='bg-white shadow-xl rounded-lg max-w-lg w-full p-6 border border-red-200'>
            <div class='flex items-center mb-4'>
                <svg class='w-6 h-6 text-red-600 mr-2' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'>
                    <path stroke-linecap='round' stroke-linejoin='round' d='M18.364 5.636L5.636 18.364M5.636 5.636l12.728 12.728'></path>
                </svg>
                <h1 class='text-xl font-semibold text-red-700'>Something went wrong</h1>
            </div>

            <p class='text-gray-700 mb-6'>$message</p>

            <div class='text-right'>
                <a href='share_board.php?id=$board_id' class='inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded transition'>
                    ← Back to Share Board
                </a>
            </div>
        </div>

    </body>
    </html>";
    exit;
}

if (!$board_id || !$email || !$role) {
    showError("Missing required input fields. Please ensure all fields are filled.", $board_id);
}

// Check if user exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    showError("No user found with the provided email address.", $board_id);
}

// Check if already shared
$stmt = $pdo->prepare("SELECT * FROM board_users WHERE board_id = ? AND user_id = ?");
$stmt->execute([$board_id, $user['id']]);
if ($stmt->fetch()) {
    showError("This board is already shared with that user.", $board_id);
}

// Share the board
$stmt = $pdo->prepare("INSERT INTO board_users (board_id, user_id, role) VALUES (?, ?, ?)");
$stmt->execute([$board_id, $user['id'], $role]);

header("Location: share_board.php?id=$board_id");
exit;
