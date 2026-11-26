<?php

use PHPMailer\PHPMailer\PHPMailer;

function sendEmailNotification(string $recipient, string $subject, string $body): void
{
    $configPath = __DIR__ . '/../config/mail.local.php';
    if (!is_file($configPath) || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        return;
    }

    try {
        $config = require $configPath;
        require_once __DIR__ . '/../vendor/autoload.php';

        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = $config['host'];
        $mailer->Port = $config['port'];
        $mailer->SMTPSecure = $config['encryption'];
        $mailer->SMTPAuth = $config['username'] !== '';
        $mailer->Username = $config['username'];
        $mailer->Password = $config['password'];
        $mailer->Timeout = 5;
        $mailer->CharSet = PHPMailer::CHARSET_UTF8;
        $mailer->setFrom($config['from_email'], 'Kundeservice');
        $mailer->addAddress($recipient);
        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->send();
    } catch (Throwable $e) {
        error_log('Kunne ikke sende e-postvarsel. Kontroller SMTP-oppsettet og tilgang til e-postserveren.');
    }
}

function notifyChatRecipient(
    PDO $pdo,
    int $recipientId,
    int $senderId,
    string $conversationLabel
): void {
    if ($recipientId <= 0 || $recipientId === $senderId) {
        return;
    }

    try {
        $statement = $pdo->prepare('SELECT email FROM users WHERE user_id = :user_id');
        $statement->execute(['user_id' => $recipientId]);
        $recipient = (string) $statement->fetchColumn();

        sendEmailNotification(
            $recipient,
            'Ny melding i kundeservice',
            "Du har fått en ny melding i $conversationLabel. Logg inn i kundeservice for å lese meldingen."
        );
    } catch (PDOException $e) {
        error_log('Kunne ikke hente mottaker for e-postvarsel.');
    }
}
