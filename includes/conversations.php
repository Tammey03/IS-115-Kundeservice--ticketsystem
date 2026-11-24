<?php
require_once __DIR__ . '/../config/auth.php';

/* =========================
   START GENERELL SAMTALE
   Bare kunder kan starte samtaler.
   Herman, med bruker-ID 5, er fast mottaker.
   ========================= */
function opprettSamtale(PDO $pdo, string $message): int
{
    requireRoles(['user']);

    // Kontroller teksten før noe lagres.
    $message = trim($message);

    if ($message === '' || mb_strlen($message, 'UTF-8') > 5000) {
        throw new InvalidArgumentException(
            'Meldingen må inneholde mellom 1 og 5000 tegn.'
        );
    }

    // Samtalen og første melding skal lagres samlet.
    $pdo->beginTransaction();

    try {
        // Kontroller at den faste mottakeren er en saksbehandler.
        $statement = $pdo->prepare(
            'SELECT user_id FROM users
             WHERE user_id = :user_id AND role = :role'
        );

        $statement->execute([
            'user_id' => 5,
            'role' => 'admin',
        ]);

        if (!$statement->fetch()) {
            throw new RuntimeException(
                'Kundeservice er ikke tilgjengelig akkurat nå.'
            );
        }

        // Kunden hentes fra innloggingen, mottakeren bestemmes her.
        $statement = $pdo->prepare(
            'INSERT INTO conversations (user_id, assigned_to)
             VALUES (:user_id, :assigned_to)'
        );

        $statement->execute([
            'user_id' => $_SESSION['user_id'],
            'assigned_to' => 5,
        ]);

        $conversationId = (int) $pdo->lastInsertId();

        // Lagre kundens første melding.
        $statement = $pdo->prepare(
            'INSERT INTO conversation_messages
                (conversation_id, user_id, message)
             VALUES (:conversation_id, :user_id, :message)'
        );

        $statement->execute([
            'conversation_id' => $conversationId,
            'user_id' => $_SESSION['user_id'],
            'message' => $message,
        ]);

        $pdo->commit();

        return $conversationId;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }

}

/* =========================
   HENT GENERELL SAMTALE
   Bare kunden som startet samtalen og den
   tildelte saksbehandleren får tilgang.
   ========================= */
function hentGenerellSamtale(
    PDO $pdo,
    int $conversationId
): ?array {
    requireRoles(['user', 'admin']);

    $sql = 'SELECT conversation_id, user_id, assigned_to, created_at
            FROM conversations
            WHERE conversation_id = :conversation_id';

    if ($_SESSION['role'] === 'user') {
        $sql .= ' AND user_id = :user_id';
    } else {
        $sql .= ' AND assigned_to = :user_id';
    }

    $statement = $pdo->prepare($sql);

    $statement->execute([
        'conversation_id' => $conversationId,
        'user_id' => $_SESSION['user_id'],
    ]);

    return $statement->fetch() ?: null;
}
/* =========================
   HENT MELDINGER I GENERELL SAMTALE
   Kontrollerer tilgang og henter eldste melding først.
   ========================= */
function hentGenerelleMeldinger(
    PDO $pdo,
    int $conversationId
): array {
    if (hentGenerellSamtale($pdo, $conversationId) === null) {
        throw new RuntimeException('Ingen tilgang til samtalen.');
    }

    $statement = $pdo->prepare(
        'SELECT m.user_id, m.message, m.created_at, u.username
         FROM conversation_messages AS m
         JOIN users AS u ON u.user_id = m.user_id
         WHERE m.conversation_id = :conversation_id
         ORDER BY m.created_at ASC, m.message_id ASC'
    );

    $statement->execute([
        'conversation_id' => $conversationId,
    ]);

    return $statement->fetchAll();
}



/* =========================
   SEND MELDING I GENERELL SAMTALE
   Bare kunden og tildelt saksbehandler kan svare.
   ========================= */
function lagreGenerellMelding(
    PDO $pdo,
    int $conversationId,
    string $message
): void {
    if (hentGenerellSamtale($pdo, $conversationId) === null) {
        throw new RuntimeException('Ingen tilgang til samtalen.');
    }

    $message = trim($message);

    if ($message === '' || mb_strlen($message, 'UTF-8') > 5000) {
        throw new InvalidArgumentException(
            'Meldingen må inneholde mellom 1 og 5000 tegn.'
        );
    }

    $statement = $pdo->prepare(
        'INSERT INTO conversation_messages
            (conversation_id, user_id, message)
         VALUES (:conversation_id, :user_id, :message)'
    );

    $statement->execute([
        'conversation_id' => $conversationId,
        'user_id' => $_SESSION['user_id'],
        'message' => $message,
    ]);
}

/* =========================
   LIST GENERELLE SAMTALER
   Kunden ser sine egne.
   Saksbehandleren ser dem som er tildelt seg.
   ========================= */
function hentMineSamtaler(PDO $pdo): array
{
    requireRoles(['user', 'admin']);

    $sql = 'SELECT c.conversation_id, c.created_at, u.username
            FROM conversations AS c
            JOIN users AS u ON u.user_id = c.user_id';

    if ($_SESSION['role'] === 'user') {
        $sql .= ' WHERE c.user_id = :user_id';
    } else {
        $sql .= ' WHERE c.assigned_to = :user_id';
    }

    $sql .= ' ORDER BY c.created_at DESC, c.conversation_id DESC';

    $statement = $pdo->prepare($sql);
    $statement->execute([
        'user_id' => $_SESSION['user_id'],
    ]);

    return $statement->fetchAll();
}