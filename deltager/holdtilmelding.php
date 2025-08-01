<?php
require_once __DIR__ . '/../private/auth.php';
require_once __DIR__ . '/../private/dbconfig.php';

// Aktivitet-id fra GET
$aktivitetId = isset($_GET['aktivitet_id']) ? (int) $_GET['aktivitet_id'] : 0;
$deltagerId = $_SESSION['bruger_id'] ?? 0;

if (!$aktivitetId || !$deltagerId) {
  die("Ugyldig adgang.");
}

// Tjek om deltageren allerede er tilmeldt
$stmt = $pdo->prepare("SELECT COUNT(*) FROM aktivitet_deltager WHERE aktivitet_id = ? AND deltager_id = ?");
$stmt->execute([$aktivitetId, $deltagerId]);
$alleredeTilmeldt = $stmt->fetchColumn();

// Hent aktivitetens detaljer
$stmt = $pdo->prepare("SELECT titel, beskrivelse, adresse, starttid, sluttid FROM aktiviteter WHERE id = ?");
$stmt->execute([$aktivitetId]);
$aktivitet = $stmt->fetch();

if (!$aktivitet) {
  echo "Aktiviteten blev ikke fundet.";
  exit;
}

// START HTML
?>

<h2><?= htmlspecialchars($aktivitet['titel']) ?></h2>
<p><?= htmlspecialchars($aktivitet['beskrivelse']) ?></p>

<?php if ($alleredeTilmeldt > 0): ?>
  <p>✅ Du er allerede tilmeldt dette hold.</p>
<?php else: ?>
  <form method="POST">
    <input type="hidden" name="aktivitet_id" value="<?= $aktivitetId ?>">
    <button type="submit">Tilmeld dig</button>
  </form>
<?php endif; ?>

<?php
// Håndtér formular-indsending
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $pdo->prepare("INSERT INTO aktivitet_deltager (aktivitet_id, deltager_id) VALUES (?, ?)");
  $stmt->execute([$aktivitetId, $deltagerId]);
  header("Location: takfortilmelding.php?id=$aktivitetId");
  exit;
}
