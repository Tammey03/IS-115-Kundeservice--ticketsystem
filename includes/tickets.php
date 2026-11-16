<?php
/*

Hovedformålet med denne filen er å samle databaseoperasjoner for saker (tickets) på ett sted. 
Dette gjør det enklere å gjenbruke kode og holde logikken adskilt fra presentasjonen.

Funksjonene krever en ferdig opprettet PDO-tilkobling.
Databasefeil håndteres av siden som kaller funksjonene.
Lister hentes uten sideinndeling, noe som kan bli mindre
effektivt når antallet saker øker.

*/
 
require_once __DIR__ . '/../config/auth.php';

// Brukeren kan bare hente egne saker. Eieren leses fra session, ikke fra URL.
// Returnerer sakene som en liste med nyeste først, eller en tom liste uten saker.
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
// Returnerer saken som et array, eller null hvis saksnummeret ikke finnes.
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
// Henter alle saker for administratorer, med nyeste først og eventuell saksbehandler.
function hentAlleTickets(PDO $pdo): array
{
    requireRoles(['admin']);
    // LEFT JOIN beholder også saker som ikke har fått en saksbehandler.
    $statement = $pdo->query(
        'SELECT t.ticket_id, t.title, t.created_at, t.status, t.category, t.priority,
                t.description, t.assigned_to, u.username AS assigned_to_name
         FROM tickets AS t
         LEFT JOIN users AS u ON u.user_id = t.assigned_to
         ORDER BY t.created_at DESC, t.ticket_id DESC'
    );
    return $statement->fetchAll();
}

// Tildel saken til innlogget admin. Én atomisk oppdatering hindrer at to tar samme sak.
// Returnerer true hvis saken ble tildelt, ellers false.
function taTicket(PDO $pdo, int $ticketId): bool
{
    requireRoles(['admin']);
    if ($ticketId < 1) {
        throw new InvalidArgumentException('Ugyldig saksnummer.');
    }
    // Statuskravet sjekkes i samme UPDATE som tildelingen, slik at bare
    // en sak som fortsatt er åpen kan tas, også ved samtidige forespørsler.
    $statement = $pdo->prepare(
        "UPDATE tickets SET status = 'in_progress', assigned_to = :assigned_to
         WHERE ticket_id = :ticket_id AND status = 'open'"
    );
    $statement->execute([
        'assigned_to' => (int) $_SESSION['user_id'],
        'ticket_id' => $ticketId,
    ]);
    return $statement->rowCount() === 1;
}

// Oppdaterer status og prioritet samlet. Eventuell bekreftelse håndteres av siden som kaller funksjonen.
function oppdaterTicket(PDO $pdo, int $ticketId, string $status, string $priority): void
{
    requireRoles(['admin']);
    // Kontrollerer verdiene på serveren fordi skjemavalg kan manipuleres.
    if (!in_array($status, ['open', 'in_progress', 'closed'], true)
        || !in_array($priority, ['low', 'medium', 'high'], true)) {
        throw new InvalidArgumentException('Ugyldig status eller prioritet.');
    }
    $statement = $pdo->prepare(
        'UPDATE tickets SET status = :status, priority = :priority WHERE ticket_id = :ticket_id'
    );
    $statement->execute(['status' => $status, 'priority' => $priority, 'ticket_id' => $ticketId]);
}
