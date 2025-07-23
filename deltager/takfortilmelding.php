<?php
require_once __DIR__ . '/../private/dbconfig.php';

$id = $_GET['id'] ?? null;
if (!$id) die("Deltager-ID mangler");

$stmt = $pdo->prepare("
  SELECT d.navn, d.email, d.telefon, d.tilmeldt, k.id AS kommune_id, k.navn AS kommune
  FROM deltagere d
  JOIN kommuner k ON d.kommune_id = k.id
  WHERE d.id = ?
");
$stmt->execute([$id]);
$deltager = $stmt->fetch();

if (!$deltager) die("Deltager ikke fundet");

// Tjek valgte aktiviteter
$stmt = $pdo->prepare("
  SELECT a.aktivitet_id, a.titel, a.adresse, a.starttid, a.sluttid
  FROM aktiviteter a
  JOIN aktivitet_deltager ad ON ad.aktivitet_id = a.id
  WHERE ad.deltager_id = ?
");
$stmt->execute([$id]);
$valgte = $stmt->fetchAll();

// Hent øvrige aktiviteter i kommunen
$stmt = $pdo->prepare("
  SELECT a.aktivitet_id, a.titel, a.adresse, a.starttid, a.sluttid
  FROM aktiviteter a
  WHERE a.kommune_id = ? AND a.status = 'Afholdes'
  ORDER BY a.starttid ASC
");
$stmt->execute([$deltager['kommune_id']]);
$tilgængelige = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="da">
<head>
  <meta charset="UTF-8" />
  <title>Tak for din tilmelding – Pacta Tua</title>
  <link rel="stylesheet" href="/vorthold/shared/style/main.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body>

  <header>
    <h1>Tak for din tilmelding</h1>
  </header>

  <main class="velkomst">
    <img src="/vorthold/shared/images/hold_velkomst.jpg" alt="Illustration" />
    <div class="tekst">
      <h2>Vi har registreret dine oplysninger:</h2>
      <ul>
        <li><strong>Navn:</strong> <?= htmlspecialchars($deltager['navn']) ?></li>
        <li><strong>E-mail:</strong> <?= htmlspecialchars($deltager['email']) ?></li>
        <li><strong>Telefon:</strong> <?= htmlspecialchars($deltager['telefon']) ?></li>
        <li><strong>Kommune:</strong> <?= htmlspecialchars($deltager['kommune']) ?></li>
        <li><strong>Tilmeldt:</strong> <?= date("d.m.Y H:i", strtotime($deltager['tilmeldt'])) ?></li>
      </ul>

      <h2>Dine valgte aktiviteter:</h2>
      <?php if (count($valgte) === 0): ?>
        <p>Du har endnu ikke valgt nogen aktiviteter.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($valgte as $aktivitet): ?>
            <li>
              <strong><?= htmlspecialchars($aktivitet['titel']) ?></strong><br>
              <?= date("d.m.Y H:i", strtotime($aktivitet['starttid'])) ?> – <?= date("d.m.Y H:i", strtotime($aktivitet['sluttid'])) ?><br>
              Adresse: <a href="https://maps.google.com/?q=<?= urlencode($aktivitet['adresse']) ?>" target="_blank">
                <?= htmlspecialchars($aktivitet['adresse']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <h2>Aktive holdaktiviteter i din kommune:</h2>
      <?php if (count($tilgængelige) === 0): ?>
        <p>Der er i øjeblikket ingen aktive aktiviteter i <?= htmlspecialchars($deltager['kommune']) ?>.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($tilgængelige as $aktivitet): ?>
            <li>
              <strong><?= htmlspecialchars($aktivitet['titel']) ?> (<?= $aktivitet['aktivitet_id'] ?>)</strong><br>
              <?= date("d.m.Y H:i", strtotime($aktivitet['starttid'])) ?> – <?= date("d.m.Y H:i", strtotime($aktivitet['sluttid'])) ?><br>
              Adresse: <a href="https://maps.google.com/?q=<?= urlencode($aktivitet['adresse']) ?>" target="_blank">
                <?= htmlspecialchars($aktivitet['adresse']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </main>

  <footer>
    <p>Har du spørgsmål? Skriv til kontakt@ziczac.dk</p>
  </footer>

</body>
</html>
