
<?php
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/helpers.php";


$errors = [];
$passwordError = "";
$success = false;

$name = "";
$username = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Hent skjemadata
    $name = formText("name");
    $username = formText("username");
    $email = formText("email");

    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if (!is_string($password)) {
        $password = "";
    }

    if (!is_string($confirmPassword)) {
        $confirmPassword = "";
    }

    // Valider CSRF-token
    if (!validCsrfToken($_POST["csrf_token"] ?? null)) {
        http_response_code(403);
        $errors[] = "Ugyldig skjema. Last siden på nytt og prøv igjen.";
    }

    // Valider navn
    if ($name === "") {
        $errors[] = "Du må fylle ut navnet ditt.";
    } elseif (mb_strlen($name) > 100) {
        $errors[] = "Navnet kan ikke være lengre enn 100 tegn.";
    }

    // Valider brukernavn
    if ($username === "") {
        $errors[] = "Du må fylle ut et brukernavn.";
    } elseif (mb_strlen($username) > 100) {
        $errors[] = "Brukernavnet kan ikke være lengre enn 100 tegn.";
    }

    // Valider e-post
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Du må skrive inn en gyldig e-postadresse.";
    } elseif (strlen($email) > 255) {
        $errors[] = "E-postadressen er for lang.";
    }

    // Valider passord
    if (strlen($password) < 8) {
        $errors[] = "Passordet må være minst 8 tegn.";
    }

    if ($password !== $confirmPassword) {
        $passwordError = "Passordene må være like.";
        $errors[] = $passwordError;
    }

    // Registrer brukeren når skjemaet er gyldig
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                "SELECT user_id
                 FROM users
                 WHERE username = :username OR email = :email
                 LIMIT 1"
            );

            $stmt->execute([
                "username" => $username,
                "email" => $email
            ]);

            if ($stmt->fetch()) {
                $errors[] = "Brukernavnet eller e-postadressen er allerede registrert.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    "INSERT INTO users
                     (name, username, email, password_hash, role)
                     VALUES (:name, :username, :email, :password_hash, 'user')"
                );

                $stmt->execute([
                    "name" => $name,
                    "username" => $username,
                    "email" => $email,
                    "password_hash" => $passwordHash
                ]);

                $success = true;
            }

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = "Det oppstod en feil under registreringen. Prøv igjen.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Opprett bruker | Kundeservice</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>
    <main class="login-page">
        <section class="login-card" aria-labelledby="register-title">

            <div class="login-header">
                <p class="login-eyebrow">Kundeservice</p>
                <h1 id="register-title">Opprett bruker</h1>
            </div>

            <!-- Feilmeldinger -->
            <?php if (!empty($errors)): ?>
                <div class="login-message login-message-error" role="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Vellykket registrering -->
            <?php if ($success): ?>
                <div class="login-message" role="status">
                    Brukeren din er opprettet!
                    Du kan nå logge inn med e-postadressen og passordet ditt.
                </div>

                <a class="login-back-link" href="login.php">
                    Gå til innlogging
                </a>

            <?php else: ?>

                <!-- Registreringsskjema -->
                <form class="login-form" action="opprettBruker.php" method="post">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e(csrfToken()) ?>"
                    >

                    <div class="login-field">
                        <label for="name">Navn</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?= e($name) ?>"
                            autocomplete="name"
                            maxlength="100"
                            required
                        >
                    </div>

                    <div class="login-field">
                        <label for="username">Brukernavn</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?= e($username) ?>"
                            autocomplete="username"
                            maxlength="100"
                            required
                        >
                    </div>

                    <div class="login-field">
                        <label for="email">E-post</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= e($email) ?>"
                            autocomplete="email"
                            maxlength="255"
                            required
                        >
                    </div>

                    <div class="login-field">
                        <label for="password">Passord</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            minlength="8"
                            autocomplete="new-password"
                            aria-describedby="password-help"
                            required
                        >

                        
                    </div>

                    <div class="login-field">
                        <label for="confirm_password">Bekreft passord</label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            minlength="8"
                            autocomplete="new-password"
                            aria-invalid="<?= $passwordError !== '' ? 'true' : 'false' ?>"
                            <?= $passwordError !== ''
                                ? 'aria-describedby="confirm-password-error"'
                                : '' ?>
                            required
                        >

                        <?php if ($passwordError !== ""): ?>
                            <p
                                class="field-error"
                                id="confirm-password-error"
                                role="alert"
                            >
                                <?= e($passwordError) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="password-toggle">
                        <label>
                            <input type="checkbox" id="show-password">
                            Vis passord
                        </label>
                    </div>

                    <button type="submit">Opprett bruker</button>
                </form>

                <a class="login-back-link" href="login.php">
                    Tilbake til innlogging
                </a>

            <?php endif; ?>
        </section>
    </main>

    <script src="../js/register.js" defer></script>
</body>

</html>
