<?php
header('Content-Type: application/json');
header("Cache-Control: no-cache, must-revalidate");


require_once __DIR__ . '/../private/dbconfig.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['overskrift'], $data['beskrivelse'], $data['beskrivelse2'], $data['billede_url'])) {
  echo json_encode(['success' => false, 'message' => 'Ugyldige data']);
  exit;
}

try {
  // Antag at der kun er én række – opdater den nyeste
  $stmt = $pdo->prepare("UPDATE velkomstindhold SET overskrift = ?, beskrivelse = ?, beskrivelse2 = ?, billede_url = ? ORDER BY id DESC LIMIT 1");
  $stmt->execute([$data['overskrift'], $data['beskrivelse'], $data['beskrivelse2'], $data['billede_url']]);

  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
