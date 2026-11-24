<?php
require_once __DIR__ . "/../config/auth.php";
$errors = [];
$success = false;

$name = "";
$username = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = is_string($_POST["name"] ?? null) ? trim($_POST["name"]) : "";
    $username = is_string($_POST["username"] ?? null) ? trim($_POST["username"]) : "";
    $email = is_string($_POST["email"] ?? null) ? trim($_POST["email"]) : "";
    $password = is_string($_POST["password"] ?? null) ? $_POST["password"] : "";
    $confirmPassword = is_string($_POST["confirm_password"] ?? null) ? $_POST["confirm_password"] : "";

    if (!validCsrfToken($_POST["csrf_token"] ?? null)) {
        http_response_code(403);
        $errors[] = "Ugyldig skjema. Last siden på nytt og prøv igjen.";
    }

    if ($name === "") {
        $errors[] = "Du må fylle ut navnet ditt.";
    }

    if ($username === "") {
        $errors[] = "Du må fylle ut et brukernavn.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Du må skrive inn en gyldig e-postadresse.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Passordet må være minst 8 tegn.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passordene må være like.";
    }

    if (empty($errors)) {
        $success = true;
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
                <p>Lag en bruker for å sende inn og følge opp saker.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="login-message login-message-error" role="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="login-message" role="status">
                    Skjemaet er validert, men brukeren er ikke opprettet. Registrering er ikke tilgjengelig ennå.
                </div>
                <a class="login-back-link" href="login.php">Gå til innlogging</a>
            <?php else: ?>
                <form class="login-form" action="opprettBruker.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="login-field">
                        <label for="name">Navn</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
                    </div>

                    <div class="login-field">
                        <label for="username">Brukernavn</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
                    </div>

                    <div class="login-field">
                        <label for="email">E-post</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                    </div>

                    <div class="login-field">
                        <label for="password">Passord</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="login-field">
                        <label for="confirm_password">Bekreft passord</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit">Opprett bruker</button>
                </form>

                <a class="login-back-link" href="login.php">Tilbake til innlogging</a>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>