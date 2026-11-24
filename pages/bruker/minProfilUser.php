
<?php
require_once __DIR__ . '/../../config/auth.php';
requireRoles(['user']);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';


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

header('Location: minProfilUser.php');
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

header('Location: minProfilUser.php');
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

<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Min profil | Kundeservice</title>

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/minprofil.css">

    <script src="../../js/auth-history.js" defer></script>
</head>

<body>

<?php require __DIR__ . '/../../includes/header.php'; ?>

<main>
    <section class="profile-page">

        <div class="profile-title">
            <h1>Min profil</h1>
            <p>Din personlige informasjon</p>
        </div>

        <div class="profile-card">

            <div class="profile-header">
                <div class="profile-image" aria-hidden="true">
                    <span><?= e($initials) ?></span>
                </div>

                <div class="profile-name">
                    <h2><?= e($fullName ?: $user['username']) ?></h2>
                    <p>Kunde</p>
                </div>
            </div>

            <div class="profile-information">

                <?php if ($success !== ''): ?>
                    <div class="profile-success" role="status">
                        <?= e($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($errors): ?>
                    <div class="profile-error" role="alert">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="profile-row">
                    <span class="profile-label">Brukernavn</span>
                    <span><?= e($user['username']) ?></span>
                </div>

                <div class="profile-row">
                    <span class="profile-label">E-post</span>
                    <span><?= e($user['email']) ?></span>
                </div>

                
<div id="profile-details" <?= $activeForm === 'profile' && $errors ? 'hidden' : '' ?>>

    <div class="profile-row">
        <span class="profile-label">Fornavn</span>
        <span><?= e($firstName) ?></span>
    </div>

    <div class="profile-row">
        <span class="profile-label">Etternavn</span>
        <span><?= e($lastName) ?></span>
    </div>

    <div class="profile-row">
        <span class="profile-label">Telefonnummer</span>
        <span><?= e($phone ?: 'Ikke oppgitt') ?></span>
    </div>

    <div class="profile-row">
        <span class="profile-label">Adresse</span>
        <span><?= e($address ?: 'Ikke oppgitt') ?></span>
    </div>

    <button type="button" class="button" id="edit-profile">
        Rediger profil
    </button>
</div>

<div id="profile-edit" <?= $activeForm === 'profile' && $errors ? '' : 'hidden' ?>>

    <h3>Rediger profil</h3>

    <form method="post" class="profile-edit-form">
        <input type="hidden" name="csrf_token"
               value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="profile">

        <label for="first_name">Fornavn</label>
        <input type="text" id="first_name" name="first_name"
               value="<?= e($firstName) ?>"
               maxlength="100" required>

        <label for="last_name">Etternavn</label>
        <input type="text" id="last_name" name="last_name"
               value="<?= e($lastName) ?>"
               maxlength="100" required>

        <label for="phone">Telefonnummer</label>
        <input type="tel" id="phone" name="phone"
               value="<?= e($phone) ?>" maxlength="20">

        <label for="address">Adresse</label>
        <input type="text" id="address" name="address"
               value="<?= e($address) ?>" maxlength="255">

        <div class="profile-actions">
            <button type="submit" class="button">
                Lagre profil
            </button>

            <button type="button" class="button" id="cancel-profile">
                Avbryt
            </button>
            
        </div>
    </form>
</div>

<div id="password-details"
     <?= $activeForm === 'password' && $errors ? 'hidden' : '' ?>>

    <button type="button" class="button" id="edit-password">
        Endre passord
    </button>
</div>

<div id="password-edit"
     <?= $activeForm === 'password' && $errors ? '' : 'hidden' ?>>


                <h3>Endre passord</h3>

                <form method="post" class="profile-edit-form">
                    <input type="hidden" name="csrf_token"
                           value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="action" value="password">

                    <label for="current_password">
                        Nåværende passord
                    </label>
                    <input type="password"
                           id="current_password"
                           name="current_password"
                           autocomplete="current-password"
                           required>

                    <label for="new_password">Nytt passord</label>
                    <input type="password"
                           id="new_password"
                           name="new_password"
                           autocomplete="new-password"
                           minlength="8" required>

                    <label for="confirm_password">
                        Bekreft nytt passord
                    </label>
                    <input type="password"
                           id="confirm_password"
                           name="confirm_password"
                           autocomplete="new-password"
                           minlength="8" required>

                    <button type="submit" class="button">
                        Endre passord
                    </button>
                    <button type="button" class="button" id="cancel-password">
    Avbryt
</button>
                </form>
                </div>

            </div>
        </div>
    </section>
</main>

<footer class="footer">
    <p>&copy; <?= date('Y') ?> Kundeservice</p>
</footer>

<script src="../../js/navBar.js"></script>
<script src="../../js/minprofil.js" defer></script>
</body>
</html>
