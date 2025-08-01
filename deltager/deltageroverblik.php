<?php
require_once __DIR__ . '/../private/auth.php';
require_once __DIR__ . '/../private/dbconfig.php';

$brugerId = $_SESSION['bruger_id'] ?? null;

// Hent brugerens data
$stmt = $pdo->prepare("SELECT * FROM deltagere WHERE id = ?");
$stmt->execute([$brugerId]);
$bruger = $stmt->fetch();

if (!$bruger) {
  die("Bruger ikke fundet");
}

// Hent brugerens aktiviteter + status
$stmt = $pdo->prepare("
  SELECT a.titel, a.adresse, a.starttid, a.sluttid, a.status
  FROM aktiviteter a
  JOIN aktivitet_deltager ad ON ad.aktivitet_id = a.id
  WHERE ad.deltager_id = ?
  ORDER BY a.starttid ASC
");
$stmt->execute([$brugerId]);
$aktiviteter = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="da">
<head>
  <meta charset="UTF-8">
  <title>Deltageroverblik</title>
  <link rel="stylesheet" href="/vorthold/shared/style/main.css" />
</head>

<header>
  <h1 id="site-title">Pacta Tura, velkommen tilbage: <?= htmlspecialchars($bruger['navn']) ?></h1>
</header>

<body>
  <main>
    <section class="velkomst">
    <img src="/vorthold/shared/images/hold_velkomst.jpg" alt="Illustration" />
      <div class="tekst">

        <h2>Dine tilmeldte kurser/hold</h2>
        <?php if (count($aktiviteter) === 0): ?>
          <p>Du har endnu ikke tilmeldt dig nogen aktiviteter.</p>
        <?php else: ?>
          <table>
            <tr>
              <th>Titel</th>
              <th>Dato</th>
              <th>Tidspunkt</th>
              <th>Adresse</th>
              <th>Status</th>
            </tr>
            <?php foreach ($aktiviteter as $aktivitet): ?>
              <tr>
                <td><?= htmlspecialchars($aktivitet['titel']) ?></td>
                <td><?= date("d.m.Y", strtotime($aktivitet['starttid'])) ?> – <?= date("d.m.Y", strtotime($aktivitet['sluttid'])) ?></td>
                <td><?= date("H:i", strtotime($aktivitet['starttid'])) ?>
                <td><a href="https://maps.google.com/?q=<?= urlencode($aktivitet['adresse']) ?>" target="_blank"><?= htmlspecialchars($aktivitet['adresse']) ?></a></td>
                <td><?= htmlspecialchars($aktivitet['status']) ?></td>
              </tr>
            <?php endforeach; ?>
          </table>
        <?php endif; ?>

        <p><a href="/vorthold/index.html">Tilbage til forsiden</a></p>

      </div>
    </section>
  </main>
</body>
</html>
