<?php
require_once __DIR__ . '/../private/auth.php';
require_once __DIR__ . '/../private/dbconfig.php';

$brugerId = $_SESSION['bruger_id'] ?? null; // Tilføj denne linje

if (!$brugerId) {
  // Sessionen mangler – brugeren sendes til login eller landingpage
  header("Location: /vorthold/index.html?login=required");
  exit();
}

require_once __DIR__ . '/../private/dbconfig.php';
$stmt = $pdo->prepare("
  SELECT d.navn, d.email, d.telefon, d.tilmeldt, k.id AS kommune_id, k.navn AS kommune
  FROM deltagere d
  JOIN kommuner k ON d.kommune_id = k.id
  WHERE d.id = ?
");
$stmt->execute([$brugerId]);  // 💡 Her skal du bruge session-ID

$deltager = $stmt->fetch();

if (!$deltager) die("Deltager ikke fundet");

// Tjek valgte aktiviteter
$stmt = $pdo->prepare("
  SELECT a.aktivitet_id, a.titel, a.adresse, a.starttid, a.sluttid
  FROM aktiviteter a
  JOIN aktivitet_deltager ad ON ad.aktivitet_id = a.id
  WHERE ad.deltager_id = ?
");
$stmt->execute([$brugerId]);  // 💡 Igen: brug session-ID

$valgte = $stmt->fetchAll();

// Hent øvrige aktiviteter i kommunen
$stmt = $pdo->prepare("
  SELECT a.id, a.aktivitet_id, a.titel, a.adresse, a.starttid, a.sluttid
  FROM aktiviteter a
  WHERE a.kommune_id = ? AND a.status = 'Afholdes'
  ORDER BY a.starttid ASC
");
$stmt->execute([$deltager['kommune_id']]);  // 💡 Her bruger du kommunens ID, som du har hentet

$tilgængelige = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="da">
<head>
  <meta charset="UTF-8" />
  <title>Tak for din registerering på – Pacta Tua</title>
  <link rel="stylesheet" href="/vorthold/shared/style/main.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

  <header>
    <h1>Tak for din tilmelding </h1>
  </header>

  <main class="velkomst">
    <img src="/vorthold/shared/images/hold_velkomst.jpg" alt="Illustration" />
    <div class="tekst">
      <h3>Vi har disse dine oplysninger fra d. <?= date("d.m.Y", strtotime($deltager['tilmeldt'])) ?></h3>
      <table>
        <tr>
          <th>
            Navn:
          </th>
          <td>
            <?= htmlspecialchars($deltager['navn']) ?>
          </td> 
          <th>
            Kommune:
          </th>
          <td>
            <?= htmlspecialchars($deltager['kommune']) ?>
          </td>

        </tr>
        <tr>
          <th>
            E-mail:
          </th>
          <td>
            <?= htmlspecialchars($deltager['email']) ?>
          </td>
           <th>
            Telefon:
          </th>
          <td>
            <?= htmlspecialchars($deltager['telefon']) ?>
          </td>
        </tr>
      </table>
          
      <h3>Dine valgte aktiviteter:</h3>
      <?php if (count($valgte) === 0): ?>
        <p>Du har endnu ikke valgt nogen aktiviteter.</p>
      <?php else: ?>
        <table>
            <tr>
                <tr> 
                    <th>Opstarts dato</th>
                    <th>Kursus / Hold</th>
                    <th>Tilmelding</th>
                </tr>  
                <?php foreach ($valgte as $aktivitet): ?>
                <tr>
                  <td>
                    <?= date("d.m.Y / H:i", strtotime($aktivitet['starttid'])) ?> 
                  </td>
                  <td>
                    <strong><?= htmlspecialchars($aktivitet['titel']) ?></strong><br>
                    Adresse: <a href="https://maps.google.com/?q=<?= urlencode($aktivitet['adresse']) ?>" target="_blank">
                    <?= htmlspecialchars($aktivitet['adresse']) ?> <i class="material-icons">&#xe55e;</i>
                  </td>
                  <td>
                    <!-- Mere info-knap med link til holdtilmelding.php -->
                    <form action="mitholdinfo.php" method="GET">
                      <input type="hidden" name="aktivitet_id" value="<?= $aktivitet['aktivitet_id'] ?>">
                      <button type="submit" class="mere-info-knap">Mere info</button>
                    </form>
                  </td>  
                </tr>
              </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
    
      <h3>Aktive holdaktiviteter i din kommune du kan tilmelde dig</h3>
        <?php if (count($tilgængelige) === 0): ?>
        <p>Der er i øjeblikket ingen aktive aktiviteter i <?= htmlspecialchars($deltager['kommune']) ?>.</p>
        <?php else: ?>
            <table>
                <tr> 
                    <th>Kurser / Hold</th>
                    <th>Opstart dato</th>
                    <th>Tilmelding</th>
                </tr>  
                <?php foreach ($tilgængelige as $aktivitet): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($aktivitet['titel']) ?> (<?= $aktivitet['aktivitet_id'] ?>)</strong><br>
                        Adresse: <a href="https://maps.google.com/?q=<?= urlencode($aktivitet['adresse']) ?>" target="_blank"> 
                        <?= htmlspecialchars($aktivitet['adresse']) ?> <i class="material-icons">&#xe55e;</i>
                        </a>
                    </td>
                    <td>
                        <?= date("d.m.Y / H:i", strtotime($aktivitet['starttid'])) ?> 
                    </td>
                    <td>
                        <!-- Mere info-knap med link til holdtilmelding.php -->
                      <form action="holdtilmelding.php" method="GET">
                        <input type="hidden" name="aktivitet_id" value="<?= $aktivitet['id'] ?>">
                        <button type="submit" class="mere-info-knap">Mere info</button>
                      </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

    </div>
  </main>

  <footer>
    <p>Har du spørgsmål? Skriv til kontakt@ziczac.dk</p>
  </footer>

</body>
</html>
