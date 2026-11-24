<?php
/*
Filen er en saksoversikt for brukere, 
og viser en tabell med alle sakene som er sendt inn av den innloggede brukeren.

Saksdetaljene åpnes i en dialog som krever JavaScript.
Alle brukerens saker hentes samtidig uten sideinndeling,
noe som kan bli mindre effektivt ved mange saker.
*/

require_once __DIR__ . '/../../config/auth.php';
requireRoles(['user']);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/tickets.php';
require_once __DIR__ . '/../../includes/ticketLabels.php';

$tickets = [];
$loadError = '';


try {
    /* 
    Henter bare sakene til den innloggede brukeren, med nyeste først.
    Funksjonen bruker bruker-ID fra sesjonen for å avgrense resultatet.
    */
    $attachmentsByTicket = hentTicketVedlegg($pdo);
    $tickets = hentMineTickets($pdo);
} catch (PDOException $e) {
    /* Logger tekniske detaljer for feilsøking, mens brukeren får
         en generell melding som ikke avslører databaseinformasjon. */
    error_log('Kunne ikke hente mine saker: ' . $e->getMessage());
    http_response_code(500);
    $loadError = 'Kunne ikke hente sakene dine. Prøv igjen senere.';
}

?>
<!DOCTYPE html>
<html lang="no">

<head>
    <script src="../../js/auth-history.js" defer></script>
    <script src="../../js/ticketAttachmentGallery.js" defer></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kundeservice</title>

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/mineSaker.css?v=<?= filemtime(__DIR__ . '/../../css/mineSaker.css') ?>">

</head>

<body>
    
    <?php require __DIR__ . '/../../includes/header.php'; ?>

    

    <main>

        <section class="mine-saker">

            <div class="saker-header">
                <div>
                   <?php $firstName = currentUserFirstName($pdo); ?>
                   <h1><br>Velkommen<?php if ($firstName !== ''): ?>, <?= htmlspecialchars(
                        $firstName,
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    ) ?><?php endif; ?>!</h1>
                    
                    
                    
                    
                    
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
                    <th>Detaljer</th>
                </tr>
            </thead>

            <tbody>

                <?php
                // Skiller databasefeil fra en tom saksliste, slik at en feil
                // ikke vises som om brukeren mangler saker.
                ?>
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
                    <td>
                        <a href="../chat.php?saksnummer=<?= (int) $ticket['ticket_id'] ?>"
                            class="button details-link messages-link"
                        >
                             Meldinger
                        </a>
                        <button type="button" class="button details-link" data-ticket-dialog="sak-<?= (int) $ticket['ticket_id'] ?>" aria-haspopup="dialog" aria-label="Se sak #<?= (int) $ticket['ticket_id'] ?>">Se sak</button>
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
                                <?php $attachments = $attachmentsByTicket[$ticket['ticket_id']] ?? []; ?>
                                <?php if ($attachments): ?>
                                    <dt>Vedlegg</dt>
                                    <dd><?php require __DIR__ . '/../../includes/ticketAttachmentPreview.php'; ?></dd>
                                <?php endif; ?>
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
