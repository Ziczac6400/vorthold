<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../private/dbconfig.php';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
  $stmt = $pdo->query("SELECT * FROM velkomstindhold ORDER BY id DESC LIMIT 1");
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  echo json_encode($data);
} catch (PDOException $e) {
  echo json_encode(['error' => $e->getMessage()]);
}
?>
