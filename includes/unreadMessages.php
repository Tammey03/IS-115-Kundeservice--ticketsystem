
<?php
require_once __DIR__ . '/../config/auth.php';

/* =========================
   HENT ULeste MELDINGER
   Gjelder både saker og generelle samtaler.
   ========================= */

function hentUlesteMeldinger(PDO $pdo, string $type): array
{
    requireRoles(['user', 'admin']);

    // Velg riktig tabell basert på meldingstype.
    if ($type === 'ticket') {
        $messageTable = 'ticket_messages';
        $parentTable = 'tickets';
        $idColumn = 'ticket_id';
    } elseif ($type === 'conversation') {
        $messageTable = 'conversation_messages';
        $parentTable = 'conversations';
        $idColumn = 'conversation_id';
    } else {
        throw new InvalidArgumentException('Ugyldig meldingstype.');
    }

    $userId = (int) $_SESSION['user_id'];

    // Kunden må eie samtalen.
    // Administratoren må være tildelt samtalen.
    $accessColumn = $_SESSION['role'] === 'user'
        ? 'user_id'
        : 'assigned_to';

    $sql = "SELECT DISTINCT m.$idColumn
            FROM $messageTable AS m
            JOIN $parentTable AS p
                ON p.$idColumn = m.$idColumn
            LEFT JOIN message_reads AS r
                ON r.message_id = m.message_id
                AND r.message_type = :message_type
                AND r.user_id = :reader_id
            WHERE p.$accessColumn = :owner_id
                AND m.user_id != :sender_id
                AND r.message_id IS NULL";

    $statement = $pdo->prepare($sql);

    $statement->execute([
        'message_type' => $type,
        'reader_id' => $userId,
        'owner_id' => $userId,
        'sender_id' => $userId,
    ]);

    return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
}

/* =========================
   SJEKK OM BRUKEREN HAR
   ULESTE MELDINGER
   ========================= */

function harUlesteMeldinger(PDO $pdo): bool
{
    return !empty(hentUlesteMeldinger($pdo, 'ticket'))
        || !empty(hentUlesteMeldinger($pdo, 'conversation'));
}

/* =========================
   MARKER VISTE MELDINGER SOM LEST
   ========================= */

function markerMeldingerSomLest(
    PDO $pdo,
    string $type,
    int $conversationId,
    array $messages
): void {
    requireRoles(['user', 'admin']);

    if ($type === 'ticket') {
        $messageTable = 'ticket_messages';
        $parentTable = 'tickets';
        $idColumn = 'ticket_id';
    } elseif ($type === 'conversation') {
        $messageTable = 'conversation_messages';
        $parentTable = 'conversations';
        $idColumn = 'conversation_id';
    } else {
        throw new InvalidArgumentException('Ugyldig meldingstype.');
    }

    $userId = (int) $_SESSION['user_id'];

    $accessColumn = $_SESSION['role'] === 'user'
        ? 'user_id'
        : 'assigned_to';

    $sql = "INSERT IGNORE INTO message_reads
                (message_type, message_id, user_id)
            SELECT :message_type, m.message_id, :reader_id
            FROM $messageTable AS m
            JOIN $parentTable AS p
                ON p.$idColumn = m.$idColumn
            WHERE m.$idColumn = :conversation_id
                AND m.message_id = :message_id
                AND p.$accessColumn = :owner_id
                AND m.user_id != :sender_id";

    $statement = $pdo->prepare($sql);

    foreach ($messages as $message) {
        $statement->execute([
            'message_type' => $type,
            'reader_id' => $userId,
            'conversation_id' => $conversationId,
            'message_id' => (int) $message['message_id'],
            'owner_id' => $userId,
            'sender_id' => $userId,
        ]);
    }
}
