<?php
// Felles sesjonsoppsett. Må lastes før HTML eller annen utskrift.
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, private');
header('Pragma: no-cache');
header('Expires: 0');

function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validCsrfToken($token): bool
{
    return is_string($token) && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function currentUserFirstName(?PDO $pdo = null): string
{
    if (!array_key_exists('first_name', $_SESSION) && isset($_SESSION['user_id'])) {
        if ($pdo === null) {
            require __DIR__ . '/database.php';
        }

        $statement = $pdo->prepare('SELECT first_name FROM users WHERE user_id = :user_id');
        $statement->execute(['user_id' => $_SESSION['user_id']]);
        $firstName = $statement->fetchColumn();
        $_SESSION['first_name'] = is_string($firstName) ? trim($firstName) : '';
    }

    return $_SESSION['first_name'] ?? '';
}

function requireRoles(array $roles): void
{
    if (!isset($_SESSION['user_id'])) {
        // Fungerer også for direkte forespørsler til includes og felles sider.
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $pagesPosition = strrpos($script, '/pages/');
        $basePath = $pagesPosition === false ? '' : substr($script, 0, $pagesPosition);
        header('Location: ' . $basePath . '/pages/login.php');
        exit;
    }
    if (!in_array($_SESSION['role'] ?? null, $roles, true)) {
        http_response_code(403);
        exit('Du har ikke tilgang til denne siden.');
    }
}
