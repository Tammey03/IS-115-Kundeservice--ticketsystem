<?php
require_once __DIR__ . '/../../config/auth.php';
requireRoles(['admin']);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/tickets.php';

$tickets = [];
$loadError = '';
$takeError = '';
$statusClasses = [
    'open' => 'status-apen',
    'in_progress' => 'status-behandles',
    'closed' => 'status-lost',
];

// Oversett databasens statusverdier til norsk i visningen.
$statusLabels = ['open' => 'Mottatt', 'in_progress' => 'Under behandling', 'closed' => 'Løst'];
$priorityLabels = ['low' => 'Lav', 'medium' => 'Normal', 'high' => 'Høy'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticketId = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            $takeError = 'Skjemaet er utløpt. Last siden på nytt og prøv igjen.';
        } elseif (($_POST['action'] ?? '') !== 'take' || !$ticketId) {
            http_response_code(400);
            $takeError = 'Ugyldig forespørsel eller saksnummer.';
        } elseif (!taTicket($pdo, $ticketId)) {
            http_response_code(409);
            $takeError = 'Saken er allerede tatt, avsluttet eller finnes ikke lenger.';
        } else {
            $_SESSION['ticket_message'] = 'Saken er tatt og satt under behandling.';
            header('Location: behandleSak.php?saksnummer=' . $ticketId);
            exit;
        }
    }
    $tickets = hentAlleTickets($pdo);
    // Felleskøen viser bare saker som fortsatt venter på behandling.
    $tickets = array_filter($tickets, function ($ticket) {
        return $ticket['status'] === 'open';
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
    <script src="../../js/auth-history.js" defer></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kundeservice</title>

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/mineSaker.css?v=<?= filemtime(__DIR__ . '/../../css/mineSaker.css') ?>">
</head>

<body>
    
    <!-- =========================
         NAVIGASJON
         ========================= -->
 <?php require __DIR__ . '/includes/headerAdmin.php'; ?>

    <main>
        <!-- =========================
             HOVEDINNHOLD
             ========================= -->
             <!-- Introduksjon -->
        <section class="mine-saker">
            <div class="saker-header">
                <div>
                    <h1>Alle saker</h1>
                    <p>Her finner du oversikt over alle sakene som er sendt inn av brukere.</p>
                </div>
            </div>

            <?php if ($takeError !== ''): ?>
                <p role="alert"><?= htmlspecialchars($takeError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php endif; ?>
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
                    <td colspan="5">Ingen saker venter på behandling.</td>
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
                        <div class="sak-rad-knapper">
                        <button type="button" class="button details-link" data-ticket-dialog="sak-<?= (int) $ticket['ticket_id'] ?>" aria-haspopup="dialog" aria-label="Se sak #<?= (int) $ticket['ticket_id'] ?>">Se sak</button>
                            <form method="post">
                                <input type="hidden" name="csrf_token"
                                     value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

                                <input type="hidden" name="ticket_id"
                                    value="<?= (int) $ticket['ticket_id'] ?>">

                                <button type="submit" name="action" value="take"
                                     class="button details-link">
                                         Ta sak
                                </button>
                            </form>
                        </div>
                        <?php // Innholdet vises først når JavaScript kopierer det til dialogen. ?>
                        <template id="sak-<?= (int) $ticket['ticket_id'] ?>">
                            <h2 id="sak-dialog-title">Sak #<?= (int) $ticket['ticket_id'] ?>: <?= htmlspecialchars($ticket['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                            <dl class="sak-details">
                                <dt>Sendt inn</dt>
                                <dd><?= htmlspecialchars((new DateTimeImmutable($ticket['created_at']))->format('d.m.Y H:i'), ENT_QUOTES, 'UTF-8') ?></dd>
                                <dt>Status</dt>
                                <dd><?= htmlspecialchars($statusLabels[$ticket['status']] ?? $ticket['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
                                <dt>Kategori</dt>
                                <dd><?= htmlspecialchars($ticket['category'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
                                <dt>Prioritet</dt>
                                <dd><?= htmlspecialchars($priorityLabels[$ticket['priority']] ?? $ticket['priority'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
                                <dt>Beskrivelse</dt>
                                <dd class="sak-description"><?= htmlspecialchars($ticket['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
                            </dl>
                        </template>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>

            </tbody>

        </table>
            </div>
        </section>
    </main>
    <!-- showModal() legger dialogen over siden og holder tastaturfokus inne i den. -->
    <dialog id="sak-dialog" class="sak-dialog" aria-labelledby="sak-dialog-title">
        <form method="dialog" class="sak-dialog-close">
            <button type="submit" class="button" autofocus>Lukk ✕</button>
        </form>
        <div id="sak-dialog-content"></div>
    </dialog>

    <footer>
        <!-- Footer kommer her -->
    </footer>

    <script src="../../js/navBar.js"></script>
    <script src="../../js/mineSaker.js"></script>
</body>

</html>
