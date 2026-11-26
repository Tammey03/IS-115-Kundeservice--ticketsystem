<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../includes/passwordReset.php';

header('Referrer-Policy: no-referrer');

$resetMode = isset($_GET['bruker']) || isset($_GET['token']);
$userId = filter_var($_GET['bruker'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$token = $_GET['token'] ?? '';
$invalidLink = 'Lenken er ugyldig eller utløpt. Be om en ny lenke.';
$validReset = false;
$error = '';
$message = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !validCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = 'Skjemaet er utløpt. Prøv igjen.';
    } elseif ($resetMode) {
        if ($userId && is_string($token) && preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            require __DIR__ . '/../config/database.php';
            $reset = findPasswordReset($pdo, $userId, $token);
            $validReset = $reset !== null;
        }

        if (!$validReset) {
            $error = $invalidLink;
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmation = $_POST['confirm_password'] ?? '';

            if (!is_string($password) || strlen($password) < 8 || strlen($password) > 72
                || str_contains($password, "\0")) {
                $error = 'Det nye passordet må være minst 8 tegn og maksimalt 72 byte.';
            } elseif ($password !== $confirmation) {
                $error = 'Passordene er ikke like.';
            } elseif (resetPassword($pdo, $userId, $reset, $password)) {
                sendEmailNotification(
                    $reset['email'],
                    'Passordet ditt er endret',
                    'Passordet til kundeservicekontoen din er endret. '
                    . 'Kontakt kundeservice hvis du ikke gjorde denne endringen.'
                );
                $_SESSION['password_reset_success'] = 'Passordet ditt er endret. Du kan nå logge inn.';
                header('Location: login.php', true, 303);
                exit;
            } else {
                $validReset = false;
                $error = $invalidLink;
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = formText('email');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Skriv inn en gyldig e-postadresse.';
        } else {
            require __DIR__ . '/../config/database.php';
            $pageUrl = getenv('PASSWORD_RESET_URL')
                ?: 'http://localhost' . dirname($_SERVER['SCRIPT_NAME']) . '/glemtPassord.php';
            sendPasswordResetLink($pdo, $email, $pageUrl);
            $message = 'Hvis adressen er registrert, får du en e-post med en lenke for å velge nytt passord.';
        }
    }
} catch (PDOException $e) {
    error_log('Feil ved tilbakestilling av passord: ' . $e->getMessage());
    http_response_code(500);
    $error = 'Tilbakestilling er midlertidig utilgjengelig. Prøv igjen senere.';
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $resetMode ? 'Nytt passord' : 'Glemt passord' ?> | Kundeservice</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login.css">
    <?php if ($validReset): ?>
        <script src="../js/register.js" defer></script>
    <?php endif; ?>
</head>
<body>
    <main class="login-page">
        <section class="login-card" aria-labelledby="forgot-password-title">
            <div class="login-header">
                <p class="login-eyebrow">Kundeservice</p>
                <h1 id="forgot-password-title"><?= $resetMode ? 'Velg nytt passord' : 'Glemt passord?' ?></h1>
            </div>

            <?php if ($error !== ''): ?>
                <div class="login-message login-message-error" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <?php if ($message !== ''): ?>
                <div class="login-message" role="status"><?= e($message) ?></div>
            <?php elseif (!$resetMode || $validReset): ?>
                <form class="login-form" method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                    <?php if ($resetMode): ?>
                        <div class="login-field">
                            <label for="password">Nytt passord</label>
                            <input type="password" id="password" name="password" autocomplete="new-password" minlength="8" required>
                        </div>
                        <div class="login-field">
                            <label for="confirm_password">Bekreft nytt passord</label>
                            <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" minlength="8" required>
                        </div>
                        <div class="password-toggle">
                            <label>
                                <input type="checkbox" id="show-password">
                                Vis passord
                            </label>
                        </div>
                    <?php else: ?>
                        <div class="login-field">
                            <label for="email">E-post</label>
                            <input type="email" id="email" name="email" autocomplete="email" required>
                        </div>
                    <?php endif; ?>

                    <button type="submit"><?= $resetMode ? 'Lagre nytt passord' : 'Send lenke' ?></button>
                </form>
            <?php endif; ?>

            <?php if ($resetMode): ?>
                <a class="login-back-link" href="glemtPassord.php">Be om en ny lenke</a>
            <?php endif; ?>
            <a class="login-back-link" href="login.php">Tilbake til innlogging</a>
        </section>
    </main>
</body>
</html>
