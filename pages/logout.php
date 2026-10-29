<?php
require_once __DIR__ . '/../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Utlogging krever POST.');
}
if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Ugyldig forespørsel.');
}

$_SESSION = [];
$params = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires' => time() - 3600,
    'path' => $params['path'],
    'domain' => $params['domain'],
    'secure' => $params['secure'],
    'httponly' => $params['httponly'],
    'samesite' => $params['samesite'],
]);
session_destroy();
header('Location: login.php', true, 303);
exit;
