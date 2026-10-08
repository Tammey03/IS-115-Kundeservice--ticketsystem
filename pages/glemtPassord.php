<!-- <?php
$submitted = $_SERVER["REQUEST_METHOD"] === "POST";
?>
<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Glemt passord | Kundeservice</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>
    <main class="login-page">
        <section class="login-card" aria-labelledby="forgot-password-title">
            <div class="login-header">
                <p class="login-eyebrow">Kundeservice</p>
                <h1 id="forgot-password-title">Glemt passord?</h1>
                <p>Skriv inn brukernavn eller e-post, så hjelper vi deg videre.</p>
            </div>

            <?php if ($submitted): ?>
                <div class="login-message" role="status">
                    Forespørselen er mottatt. Sjekk e-posten din for videre instruksjoner.
                </div>
            <?php else: ?>
                <form class="login-form" action="glemtPassord.php" method="post">
                    <div class="login-field">
                        <label for="account">Brukernavn eller e-post</label>
                        <input type="text" id="account" name="account" required>
                    </div>

                    <button type="submit">Send lenke</button>
                </form>
            <?php endif; ?>

            <a class="login-back-link" href="login.php">Tilbake til innlogging</a>
        </section>
    </main>
</body>

</html>
            -->

<html>
    <body>
       <h1>Funker ikke enda</h1>
       <h1><a href="login.php">Login.php</a></h1>
    </body>
</html>