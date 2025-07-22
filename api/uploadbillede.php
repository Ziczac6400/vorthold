<?php
header('Content-Type: application/json');

// 📍 Justér stier til dit setup
$uploadDir = __DIR__ . '/../shared/images/';
$uploadUrl = '/vorthold/shared/images/';

// 🛡️ Sæt grænser for filtyper og størrelse
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSizeBytes = 5 * 1024 * 1024; // 5MB

// ✅ Tjek om fil er modtaget
if (!isset($_FILES['billede']) || $_FILES['billede']['error'] !== UPLOAD_ERR_OK) {
  echo json_encode(['success' => false, 'message' => 'Ingen gyldig fil modtaget']);
  exit;
}

$file = $_FILES['billede'];

// 🧪 Valider MIME-type
if (!in_array($file['type'], $allowedTypes)) {
  echo json_encode(['success' => false, 'message' => 'Filtypen er ikke tilladt']);
  exit;
}

// 📏 Tjek filstørrelse
if ($file['size'] > $maxSizeBytes) {
  echo json_encode(['success' => false, 'message' => 'Filen er for stor']);
  exit;
}

// 📦 Generér unikt navn (eksempel: billede_1699871234.jpg)
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newName = 'billede_' . time() . '.' . $ext;
$targetPath = $uploadDir . $newName;
$targetUrl = $uploadUrl . $newName;

// 🧱 Flyt og gem fil
if (move_uploaded_file($file['tmp_name'], $targetPath)) {
  echo json_encode(['success' => true, 'url' => $targetUrl]);
} else {
  echo json_encode(['success' => false, 'message' => 'Kunne ikke gemme filen']);
}
?>
