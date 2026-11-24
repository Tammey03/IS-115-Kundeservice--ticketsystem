<?php
require_once __DIR__ . '/../config/auth.php';
requireRoles(['user', 'admin']);

$attachmentId = filter_input(INPUT_GET, 'attachment_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$attachmentId) {
    http_response_code(400);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$sql = 'SELECT a.filename, a.content
        FROM ticket_attachments AS a
        INNER JOIN tickets AS t ON t.ticket_id = a.ticket_id
        WHERE a.attachment_id = :attachment_id';
$parameters = ['attachment_id' => $attachmentId];
if ($_SESSION['role'] === 'user') {
    $sql .= ' AND t.user_id = :user_id';
    $parameters['user_id'] = $_SESSION['user_id'];
}

$statement = $pdo->prepare($sql);
$statement->execute($parameters);
$attachment = $statement->fetch();
if (!$attachment) {
    http_response_code(404);
    exit;
}

$mimeType = (new finfo(FILEINFO_MIME_TYPE))->buffer($attachment['content']);
if (!in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/png'], true)) {
    http_response_code(415);
    exit;
}

header('Content-Type: ' . $mimeType);
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: frame-ancestors 'self'");
header("Content-Disposition: inline; filename*=UTF-8''" . rawurlencode($attachment['filename']));
echo $attachment['content'];
