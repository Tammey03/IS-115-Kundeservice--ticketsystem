<?php

require_once __DIR__ . '/notifications.php';

function sendPasswordResetLink(PDO $pdo, string $email, string $pageUrl): void
{
    $statement = $pdo->prepare('SELECT user_id, email, password_hash FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if (!$user) {
        return;
    }

    $token = bin2hex(random_bytes(32));
    $now = time();
    $lifetime = 30 * 60;
    $statement = $pdo->prepare(
        'UPDATE users
         SET password_reset_hash = :reset_hash, password_reset_expires_at = :expires_at
         WHERE user_id = :user_id AND password_hash = :password_hash
           AND (password_reset_expires_at IS NULL OR password_reset_expires_at <= :cooldown)'
    );
    $statement->execute([
        'reset_hash' => hash_hmac('sha256', $token, $user['password_hash']),
        'expires_at' => $now + $lifetime,
        'user_id' => $user['user_id'],
        'password_hash' => $user['password_hash'],
        'cooldown' => $now + $lifetime - 60,
    ]);

    if ($statement->rowCount() === 1) {
        $query = http_build_query(['bruker' => $user['user_id'], 'token' => $token]);
        sendEmailNotification(
            $user['email'],
            'Tilbakestill passordet ditt',
            "Åpne denne lenken for å velge et nytt passord. Lenken er gyldig i 30 minutter.\n\n"
            . $pageUrl . '?' . $query
            . "\n\nHvis du ikke ba om å tilbakestille passordet, kan du se bort fra denne e-posten."
        );
    }
}

function findPasswordReset(PDO $pdo, int $userId, string $token): ?array
{
    $statement = $pdo->prepare(
        'SELECT email, password_hash, password_reset_hash
         FROM users WHERE user_id = :user_id AND password_reset_expires_at > :now'
    );
    $statement->execute(['user_id' => $userId, 'now' => time()]);
    $user = $statement->fetch();

    if (!$user || !hash_equals(
        $user['password_reset_hash'],
        hash_hmac('sha256', $token, $user['password_hash'])
    )) {
        return null;
    }

    return $user;
}

function resetPassword(PDO $pdo, int $userId, array $user, string $password): bool
{
    $statement = $pdo->prepare(
        'UPDATE users
         SET password_hash = :new_hash, password_reset_hash = NULL, password_reset_expires_at = NULL
         WHERE user_id = :user_id AND password_hash = :old_hash
           AND password_reset_hash = :reset_hash AND password_reset_expires_at > :now'
    );
    $statement->execute([
        'new_hash' => password_hash($password, PASSWORD_DEFAULT),
        'user_id' => $userId,
        'old_hash' => $user['password_hash'],
        'reset_hash' => $user['password_reset_hash'],
        'now' => time(),
    ]);

    return $statement->rowCount() === 1;
}
