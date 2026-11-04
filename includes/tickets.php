<?php
require_once __DIR__ . '/../config/auth.php';

// Brukeren kan bare hente egne saker. Eieren leses fra session, ikke fra URL.
function hentMineTickets(PDO $pdo): array
{
    requireRoles(['user']);
    $statement = $pdo->prepare(
        'SELECT ticket_id, title, created_at, status, category, priority, description
         FROM tickets
         WHERE user_id = :user_id
         ORDER BY created_at DESC, ticket_id DESC'
    );
    $statement->execute(['user_id' => $_SESSION['user_id']]);
    return $statement->fetchAll();
}

// Hent én sak til adminsiden med en parameterisert spørring.
function hentTicket(PDO $pdo, int $ticketId): ?array
{
    requireRoles(['admin']);
    $statement = $pdo->prepare(
        'SELECT ticket_id, title, created_at, status, category, priority, description
         FROM tickets WHERE ticket_id = :ticket_id'
    );
    $statement->execute(['ticket_id' => $ticketId]);
    return $statement->fetch() ?: null;
}

// Kontroller rollen også her, slik at funksjonen ikke kan brukes av vanlige brukere.
function hentAlleTickets(PDO $pdo): array
{
    requireRoles(['admin']);
    $statement = $pdo->query(
        'SELECT ticket_id, title, created_at, status, category, priority, description
         FROM tickets
         ORDER BY created_at DESC, ticket_id DESC'
    );
    return $statement->fetchAll();
}

// Status og prioritet lagres samlet etter bekreftelsen.
function oppdaterTicket(PDO $pdo, int $ticketId, string $status, string $priority): void
{
    requireRoles(['admin']);
    if (!in_array($status, ['open', 'in_progress', 'closed'], true)
        || !in_array($priority, ['low', 'medium', 'high'], true)) {
        throw new InvalidArgumentException('Ugyldig status eller prioritet.');
    }
    $statement = $pdo->prepare(
        'UPDATE tickets SET status = :status, priority = :priority WHERE ticket_id = :ticket_id'
    );
    $statement->execute(['status' => $status, 'priority' => $priority, 'ticket_id' => $ticketId]);
}
