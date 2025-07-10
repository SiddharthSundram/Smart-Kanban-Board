<?php
// config/helpers.php

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

function format_date($date) {
    return date("d M Y", strtotime($date));
}
