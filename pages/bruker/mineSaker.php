<?php
require_once __DIR__ . '/../../config/auth.php';
requireRoles(['user']);
require_once __DIR__ . '/../../config/database.php';

$tickets = [];
$loadError = '';
$statusClasses = [
    'open' => 'status-apen',
    'in_progress' => 'status-behandles',
    'closed' => 'status-lost',
];

// Oversett databasens statusverdier til norsk i visningen.
$statusLabels = ['open' => 'Åpen', 'in_progress' => 'Under behandling', 'closed' => 'Løst'];

try {
    // Bruk alltid eieren fra session, aldri en bruker-ID fra URL eller skjema.
    $statement = $pdo->prepare(
        'SELECT ticket_id, title, created_at, status
         FROM tickets
         WHERE user_id = :user_id
         ORDER BY created_at DESC, ticket_id DESC'
    );
    $statement->execute(['user_id' => $_SESSION['user_id']]);
    $tickets = $statement->fetchAll();
} catch (PDOException $e) {
    error_log('Kunne ikke hente mine saker: ' . $e->getMessage());
    http_response_code(500);
    $loadError = 'Kunne ikke hente sakene dine. Prøv igjen senere.';
}

?>
<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kundeservice</title>

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/mineSaker.css">

</head>

<body>
    
     <?php require __DIR__ . '/includes/header.php'; ?>

    

    <main>

        <section class="mine-saker">

            <div class="saker-header">
                <div>
                    <h1><br>Velkommen, <?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>!</h1>
                    <h1>Mine saker</h1>
                    <p>Her finner du oversikt over sakene du har sendt inn.</p>
                </div>
            </div>


        <a href="opprettSak.php" class="button">
            + Opprett ny sak
        </a>


    <div class="table-container">

        <table class="saker-table">

            <thead>
                <tr>
                    <th>Saksnummer</th>
                    <th>Tittel</th>
                    <th>Sendt inn</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                <?php // Vis hentefeil før vi sjekker om sakslisten er tom. ?>
                <?php if ($loadError !== ''): ?>
                <tr>
                    <!-- colspan dekker alle fem kolonner; role="alert" varsler skjermlesere. -->
                    <td colspan="5" role="alert"><?= htmlspecialchars($loadError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                </tr>
                <?php elseif (empty($tickets)): ?>
                <tr>
                    <td colspan="5">Du har ingen saker ennå.</td>
                </tr>
                <?php else: ?>
                <?php // Lag én tabellrad for hver sak som ble hentet fra databasen. ?>
                <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <?php // (int) gjør saksnummeret til et heltall. ?>
                    <td>#<?= (int) $ticket['ticket_id'] ?></td>
                    <?php // htmlspecialchars viser tekst trygt, uten å tolke den som HTML. ?>
                    <td><?= htmlspecialchars($ticket['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <?php // Formater databasens tidspunkt som dag.måned.år. ?>
                    <td><?= htmlspecialchars((new DateTimeImmutable($ticket['created_at']))->format('d.m.Y'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php // Finn CSS-klassen for statusen; ?? '' gir tom klasse hvis statusen mangler i mappingen. ?>
                        <span class="status <?= $statusClasses[$ticket['status']] ?? '' ?>">
                            <?= htmlspecialchars($statusLabels[$ticket['status']] ?? $ticket['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </span>
                    </td>
                    <td></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>

            </tbody>

        </table>

    </div>



</section>

    </main>


    <footer>
        <!-- Footer kommer her -->
    </footer>

    <script src="../../js/navBar.js"></script>
</body>

</html>
