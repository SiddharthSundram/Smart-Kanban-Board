<?php

$keyword = $_GET['keyword'] ?? '';
$priority = $_GET['priority'] ?? '';
$label_color = $_GET['label_color'] ?? '';

$sql = "SELECT * FROM tasks WHERE 1=1";
$params = [];

if ($keyword) {
  $sql .= " AND (title LIKE ? OR description LIKE ?)";
  $params[] = "%$keyword%";
  $params[] = "%$keyword%";
}

if ($priority) {
  $sql .= " AND priority = ?";
  $params[] = $priority;
}

if ($label_color) {
  $sql .= " AND label_color = ?";
  $params[] = $label_color;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();
