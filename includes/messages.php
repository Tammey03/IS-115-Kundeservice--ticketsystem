<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/conversations.php';

// Henter saken bare hvis innlogget bruker har tilgang til samtalen.
function hentSakForSamtale(PDO $pdo, int $ticketId): ?array
{
    requireRoles(['user', 'admin']);

    $sql = 'SELECT ticket_id, title, description, user_id, assigned_to
            FROM tickets
            WHERE ticket_id = :ticket_id';

    if ($_SESSION['role'] === 'user') {
        // Kunden må eie saken.
        $sql .= ' AND user_id = :user_id';
    } else {
        // Saksbehandleren må være tildelt saken.
        $sql .= ' AND assigned_to = :user_id';
    }

    $statement = $pdo->prepare($sql);

    $statement->execute([
        'ticket_id' => $ticketId,
        'user_id' => $_SESSION['user_id'],
    ]);

    return $statement->fetch() ?: null;
}


function hentMeldinger(PDO $pdo, int $ticketId): array
{
    // Kontroller tilgang før meldingene hentes.
    if (hentSakForSamtale($pdo, $ticketId) === null) {
        throw new RuntimeException('Ingen tilgang til samtalen.');
    }

    $statement = $pdo->prepare(
        'SELECT m.user_id, m.message, m.created_at, u.username
         FROM ticket_messages AS m
         JOIN users AS u ON u.user_id = m.user_id
         WHERE m.ticket_id = :ticket_id
         ORDER BY m.created_at ASC, m.message_id ASC'
    );

    $statement->execute(['ticket_id' => $ticketId]);

    return $statement->fetchAll();
}

function hentSamtaleSaker(PDO $pdo): array
{
    requireRoles(['user', 'admin']);

    $sql = 'SELECT ticket_id, title
            FROM tickets';

    if ($_SESSION['role'] === 'user') {
        $sql .= ' WHERE user_id = :user_id';
    } else {
        $sql .= ' WHERE assigned_to = :user_id';
    }

    $sql .= ' ORDER BY created_at DESC, ticket_id DESC';

    $statement = $pdo->prepare($sql);
    $statement->execute([
        'user_id' => $_SESSION['user_id'],
    ]);

    return $statement->fetchAll();
}

function lagreMelding(PDO $pdo, int $ticketId, string $message): void
{
    // Kontroller tilgang også ved sending.
    $ticket = hentSakForSamtale($pdo, $ticketId);
    if ($ticket === null) {
        throw new RuntimeException('Ingen tilgang til samtalen.');
    }

    $message = trim($message);

    if ($message === '' || mb_strlen($message, 'UTF-8') > 5000) {
        throw new InvalidArgumentException(
            'Meldingen må inneholde mellom 1 og 5000 tegn.'
        );
    }

    $statement = $pdo->prepare(
        'INSERT INTO ticket_messages (ticket_id, user_id, message)
         VALUES (:ticket_id, :user_id, :message)'
    );

    $statement->execute([
        'ticket_id' => $ticketId,
        'user_id' => $_SESSION['user_id'],
        'message' => $message,
    ]);

    $recipientId = $_SESSION['role'] === 'admin' ? $ticket['user_id'] : $ticket['assigned_to'];

    notifyChatRecipient($pdo, (int) $recipientId, (int) $_SESSION['user_id'], "sak #$ticketId");
}
?>
