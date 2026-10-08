<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kundeservice</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>
    <main class="login-page">
      <section class="login-card" aria-labelledby="login-title">
        <div class="login-header">
          <p class="login-eyebrow">Kundeservice</p>
          <h1 id="login-title">Logg inn</h1>
          <p>Logg inn for å følge opp sakene dine.</p>
        </div>

        <form class="login-form" action="login.php" method="post">
          <div class="login-field">
            <label for="username">Brukernavn</label>
            <input type="text" id="username" name="username" required>
          </div>

          <div class="login-field">
            <label for="password">Passord</label>
            <input type="password" id="password" name="password" required>
          </div>

          <a class="forgot-password" href="glemtPassord.php">Glemt passord?</a>

          <button type="submit">Logg inn</button>
        </form>

        <p class="login-register-link">
          Ny bruker? <a href="opprettBruker.php">Opprett bruker</a>
        </p>
      </section>

      <section class="login-test-links" aria-label="Testlenker">
        <p>Lager bare en enkel login for å teste systemet. Ingen sikkerhet implementert.</p>
        <p>Brukerside: <a href="bruker/mainUser.php">mainUser.php</a></p>
        <p>Admin-side: <a href="admin/mainAdmin.php">mainAdmin.php</a></p>
      </section>
    </main>

    <script src="../js/script.js"></script>
</body>

</html>

