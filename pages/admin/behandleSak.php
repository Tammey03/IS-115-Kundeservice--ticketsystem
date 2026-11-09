<?php
require_once __DIR__ . '/../../config/auth.php';
requireRoles(['admin']);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/tickets.php';

$ticket = null;
$loadError = '';
$saveError = '';
$confirmSave = false;
$selectedStatus = '';
$selectedPriority = '';
$ticketId = filter_input(INPUT_GET, 'saksnummer', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$statusLabels = ['open' => 'Mottatt', 'in_progress' => 'Under behandling', 'closed' => 'Fullført'];
$statusClasses = ['open' => 'status-apen', 'in_progress' => 'status-behandles', 'closed' => 'status-lost'];
$priorityLabels = ['low' => 'Lav', 'medium' => 'Normal', 'high' => 'Høy'];

if (!$ticketId) {
    http_response_code(400);
    $loadError = 'Ugyldig eller manglende saksnummer.';
} else {
    try {
        $ticket = hentTicket($pdo, $ticketId);
        if ($ticket === null) {
            http_response_code(404);
            $loadError = 'Fant ikke saken.';
        }
        if ($ticket !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
                http_response_code(403);
                $saveError = 'Skjemaet er utløpt. Last siden på nytt og prøv igjen.';
            } elseif (in_array($action, ['review', 'save'], true)
                && is_string($_POST['status'] ?? null)
                && array_key_exists($_POST['status'], $statusLabels)
                && is_string($_POST['priority'] ?? null)
                && array_key_exists($_POST['priority'], $priorityLabels)) {
                $selectedStatus = $_POST['status'];
                $selectedPriority = $_POST['priority'];
                if ($action === 'review') {
                    // Vis en egen bekreftelse før noe lagres i databasen.
                    $confirmSave = true;
                    $_SESSION['ticket_review'] = [$ticketId, $selectedStatus, $selectedPriority];
                } elseif (($_SESSION['ticket_review'] ?? null) === [$ticketId, $selectedStatus, $selectedPriority]) {
                    oppdaterTicket($pdo, $ticketId, $selectedStatus, $selectedPriority);
                    unset($_SESSION['ticket_review']);
                } else {
                    $saveError = 'Kontroller endringene på nytt før du bekrefter.';
                }
            } else {
                http_response_code(400);
                $saveError = 'Ugyldig endring.';
            }
            if ($saveError === '' && !$confirmSave) {
                $_SESSION['ticket_message'] = 'Endringen er lagret.';
                header('Location: behandleSak.php?saksnummer=' . $ticketId);
                exit;
            }
        }
    } catch (PDOException $e) {
        error_log('Kunne ikke hente saken: ' . $e->getMessage());
        http_response_code(500);
        $loadError = 'Kunne ikke hente saken. Prøv igjen senere.';
    }
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <script src="../../js/auth-history.js" defer></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Behandle sak – Kundeservice</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/mineSaker.css?v=<?= filemtime(__DIR__ . '/../../css/mineSaker.css') ?>">
</head>
<body>
    <?php require __DIR__ . '/includes/headerAdmin.php'; ?>
    <main>
        <section class="mine-saker">
            <div class="saker-header">
                <h1>Behandle sak<?= $ticket ? ' #' . (int) $ticket['ticket_id'] : '' ?></h1>
                <div class ="sak-knapper">
                    <a href="behandleSaker.php" class="button details-link">Tilbake til sakene</a>
                   
                </div>
            </div>
            <div class="sak-card">
                <?php if (isset($_SESSION['ticket_message'])): ?>
                    <p role="status"><?= htmlspecialchars($_SESSION['ticket_message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                    <?php unset($_SESSION['ticket_message']); ?>
                <?php endif; ?>
                <?php if ($saveError !== ''): ?>
                    <p role="alert"><?= htmlspecialchars($saveError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                <?php endif; ?>
                <?php if ($loadError !== ''): ?>
                    <p role="alert"><?= htmlspecialchars($loadError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                <?php else: ?>
                    <h2><?= htmlspecialchars($ticket['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                    <dl class="sak-details">
                        <dt>Sendt inn</dt>
                        <dd><?= htmlspecialchars((new DateTimeImmutable($ticket['created_at']))->format('d.m.Y H:i'), ENT_QUOTES, 'UTF-8') ?></dd>
                        <dt>Status</dt>
                        <dd><span class="status <?= $statusClasses[$ticket['status']] ?? '' ?>"><?= htmlspecialchars($statusLabels[$ticket['status']] ?? $ticket['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span></dd>
                        <dt>Kategori</dt>
                        <dd><?= htmlspecialchars($ticket['category'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
                        <dt>Prioritet</dt>
                        <dd><?= htmlspecialchars($priorityLabels[$ticket['priority']] ?? $ticket['priority'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
                        <dt>Beskrivelse</dt>
                        <dd class="sak-description"><?= htmlspecialchars($ticket['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
                    </dl>
                    <?php if ($confirmSave): ?>
                        <h3>Kontroller før du lagrer</h3>
                        <p>Status: <strong><?= $statusLabels[$selectedStatus] ?></strong>.
                           Prioritet: <strong><?= $priorityLabels[$selectedPriority] ?></strong>.</p>
                        <?php if ($selectedStatus === 'closed'): ?>
                            <p>Saken markeres som fullført og fjernes fra behandlingslisten.</p>
                        <?php endif; ?>
                        <form method="post" class="sak-redigering">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="priority" value="<?= htmlspecialchars($selectedPriority, ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" name="action" value="save" class="button details-link behandle-knapp">Bekreft og lagre</button>
                            <a href="behandleSak.php?saksnummer=<?= (int) $ticketId ?>" class="button details-link">Avbryt</a>
                        </form>
                    <?php else: ?>
                        <form method="post" class="sak-redigering">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <?php foreach ($statusLabels as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= ($selectedStatus ?: ($ticket['status'] === 'open' ? 'in_progress' : $ticket['status'])) === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="priority">Prioritet</label>
                            <select id="priority" name="priority">
                                <?php foreach ($priorityLabels as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= ($selectedPriority ?: $ticket['priority']) === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="action" value="review" class="button details-link behandle-knapp">Lagre sak</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <script src="../../js/navBar.js"></script>
</body>
</html>
