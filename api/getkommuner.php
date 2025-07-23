<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../private/dbconfig.php';

try {
  $stmt = $pdo->query("SELECT id, navn FROM kommuner ORDER BY navn ASC");
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($result);
} catch (PDOException $e) {
  echo json_encode(['error' => $e->getMessage()]);
}
?>
