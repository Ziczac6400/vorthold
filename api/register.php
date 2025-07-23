<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../private/dbconfig.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['navn'], $data['email'], $data['telefon'], $data['kommune'])) {
  echo json_encode(['success' => false, 'message' => 'Manglende eller ugyldige data']);
  exit;
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['success' => false, 'message' => 'E-mail er ikke gyldig']);
  exit;
}

try {
  // ✅ Tjek om e-mail allerede findes og hent navn
  $checkStmt = $pdo->prepare("SELECT navn FROM deltagere WHERE email = ?");
  $checkStmt->execute([$data['email']]);
  $existingName = $checkStmt->fetchColumn();

  if ($existingName) {
    echo json_encode([
      'success' => false,
      'message' => "E-mailen er allerede registreret af \"$existingName\""
    ]);
    exit;
  }

  // ✅ Indsæt ny deltager
  $stmt = $pdo->prepare("
    INSERT INTO deltagere (navn, email, telefon, kommune_id)
    VALUES (?, ?, ?, ?)
  ");
  $stmt->execute([
    $data['navn'], $data['email'], $data['telefon'], $data['kommune']
  ]);

// ⭐ (7) Hent ID på nyoprettet deltager
  $id = $pdo->lastInsertId();

  // ⭐ (8) Redirect til tak-siden med ID
  $id = $pdo->lastInsertId();

  echo json_encode([
    'success' => true,
    'redirect' => "/vorthold/deltager/takfortilmelding.php?id=$id"
  ]);
  exit;


  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Databasefejl: ' . $e->getMessage()]);
}
?>
