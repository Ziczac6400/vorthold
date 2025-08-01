<?php

session_start();
require_once __DIR__ . '/../private/dbconfig.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');
$telefon = trim($data['phone'] ?? '');

if (!$email || !$telefon) {
  echo json_encode(['success' => false, 'message' => 'Udfyld baade e-mail og telefon.']);
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM deltagere WHERE email = ? AND telefon = ?");
$stmt->execute([$email, $telefon]);
$bruger = $stmt->fetch();

if ($bruger) {
  $_SESSION['bruger_id'] = $bruger['id'];
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'message' => 'Ingen bruger matchede e-mail og telefon.']);
}
exit;
?>