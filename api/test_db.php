<?php
echo "<h2>Tester databaseforbindelse...</h2>";

require_once __DIR__ . '/../private/dbconfig.php';

try {
    $stmt = $pdo->query("SELECT NOW() AS tidspunkt");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>✅ Forbindelse OK – serverens tid er: " . $row['tidspunkt'] . "</p>";
} catch (PDOException $e) {
    echo "<p>❌ Fejl ved forespørgsel: " . $e->getMessage() . "</p>";
}
?>