
<?php
require_once __DIR__ . '/../config/auth.php';
requireRoles(['user', 'admin']);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$userId = $_SESSION['user_id'];
$errors = [];
// Hent bekreftelse fra forrige forespørsel
$success = $_SESSION['profile_success'] ?? '';
unset($_SESSION['profile_success']);
$activeForm = '';

$stmt = $pdo->prepare(
    "SELECT first_name, last_name, username, email,
            phone, address, password_hash
     FROM users
     WHERE user_id = :user_id
     LIMIT 1"
);
$stmt->execute(['user_id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    exit('Brukeren ble ikke funnet.');
}

$firstName = $user['first_name'] ?? '';
$lastName = $user['last_name'] ?? '';
$phone = $user['phone'] ?? '';
$address = $user['address'] ?? '';

// Behandle skjemaer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $activeForm = formText('action');

    if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $errors[] = 'Ugyldig skjema. Last siden på nytt.';
    } elseif ($activeForm === 'profile') {

        $firstName = formText('first_name');
        $lastName = formText('last_name');
        $phone = formText('phone');
        $address = formText('address');

        if ($firstName === '' || mb_strlen($firstName) > 100) {
            $errors[] = 'Fornavn må være mellom 1 og 100 tegn.';
        }

        if ($lastName === '' || mb_strlen($lastName) > 100) {
            $errors[] = 'Etternavn må være mellom 1 og 100 tegn.';
        }

        if (mb_strlen($phone) > 20 ||
            ($phone !== '' && !preg_match('/^\+?[0-9 ]+$/', $phone))) {
            $errors[] = 'Skriv inn et gyldig telefonnummer.';
        }

        if (mb_strlen($address) > 255) {
            $errors[] = 'Adressen kan ikke være lengre enn 255 tegn.';
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare(
                    "UPDATE users
                     SET first_name = :first_name,
                         last_name = :last_name,
                         phone = :phone,
                         address = :address
                     WHERE user_id = :user_id"
                );

                $stmt->execute([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $phone !== '' ? $phone : null,
                    'address' => $address !== '' ? $address : null,
                    'user_id' => $userId
                ]);

                $_SESSION['profile_success'] = 'Profilen din er oppdatert.';

header('Location: ' . $profilePage, true, 303);
exit;
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $errors[] = 'Kunne ikke lagre profilen. Prøv igjen.';
            }
        }

    } elseif ($activeForm === 'password') {

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!is_string($currentPassword) ||
            !is_string($newPassword) ||
            !is_string($confirmPassword)) {
            $errors[] = 'Ugyldige passordfelt.';
        } else {
            if (!password_verify($currentPassword, $user['password_hash'])) {
                $errors[] = 'Nåværende passord er feil.';
            }

            if (strlen($newPassword) < 8) {
                $errors[] = 'Nytt passord må ha minst 8 tegn.';
            }

            if ($newPassword !== $confirmPassword) {
                $errors[] = 'De nye passordene er ikke like.';
            }

            if (empty($errors)) {
                try {
                    $passwordHash = password_hash(
                        $newPassword,
                        PASSWORD_DEFAULT
                    );

                    $stmt = $pdo->prepare(
                        "UPDATE users
                         SET password_hash = :password_hash
                         WHERE user_id = :user_id"
                    );

                    $stmt->execute([
                        'password_hash' => $passwordHash,
                        'user_id' => $userId
                    ]);

                    $_SESSION['profile_success'] = 'Passordet ditt er endret.';

                    header('Location: ' . $profilePage, true, 303);
                    exit;
                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    $errors[] = 'Kunne ikke endre passordet.';
                }
            }
        }

    } else {
        $errors[] = 'Ukjent handling.';
    }
}

$fullName = trim($firstName . ' ' . $lastName);

$initials = mb_strtoupper(
    mb_substr($firstName, 0, 1) .
    mb_substr($lastName, 0, 1)
);

if ($initials === '') {
    $initials = mb_strtoupper(
        mb_substr($user['username'], 0, 2)
    );
}
?>
