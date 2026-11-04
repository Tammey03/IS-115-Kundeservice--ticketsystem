<?php
require_once __DIR__ . '/../../config/auth.php';
requireRoles(['admin']);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/tickets.php';

$tickets = [];
$loadError = '';
$statusClasses = [
    'open' => 'status-apen',
    'in_progress' => 'status-behandles',
    'closed' => 'status-lost',
];

// Oversett databasens statusverdier til norsk i visningen.
$statusLabels = ['open' => 'Mottatt', 'in_progress' => 'Under behandling', 'closed' => 'Løst'];
$priorityLabels = ['low' => 'Lav', 'medium' => 'Normal', 'high' => 'Høy'];

try {
  $tickets = hentAlleTickets($pdo);

    // Fjern ferdigbehandlede saker fra listen.
    $tickets = array_filter($tickets, function ($ticket) {
        return $ticket['status'] !== 'closed';
    });
    
} catch (PDOException $e) {
    error_log('Kunne ikke hente alle saker: ' . $e->getMessage());
    http_response_code(500);
    $loadError = 'Kunne ikke hente sakene. Prøv igjen senere.';
}

?>
<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kundeservice</title>

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/mineSaker.css?v=<?= filemtime(__DIR__ . '/../../css/mineSaker.css') ?>">

</head>

<body>
    
   <?php require __DIR__ . '/includes/headerAdmin.php'; ?>

    <main>
     <section class="hero">
            <h1>Velkommen til Kundeservice</h1>
            <p>
                Dette er en kundeserviceplattform som lar deg administrere og spore saker effektivt. 
                Som admin skal du kunne behandle og svare på brukernes saker.
                Saksbehandling er ikke tilgjengelig ennå.
            </p>

            <div class="table-container">
             <table class="saker-table">

            <thead>
                <tr>
                    <th>Saksnummer</th>
                    <th>Tittel</th>
                    <th>Sendt inn</th>
                    <th>Status</th>
                    <th>Detaljer</th>
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
                    <td colspan="5">Det er ingen saker ennå.</td>
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
                    <td>
                        <a class="button details-link" href="behandleSak.php?saksnummer=<?= (int) $ticket['ticket_id'] ?>" aria-label="Behandle sak #<?= (int) $ticket['ticket_id'] ?>">Behandle</a>
                    </td>
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
