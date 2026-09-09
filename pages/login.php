<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kundeservice</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    
    <header>
        <!-- Navigasjon kommer her -->
    </header>

    <main>
      <h1>Login</h1>
      <form action="login.php" method="post">
        <label for="username">Brukernavn:</label>
        <input type="text" id="username" name="username" required><br><br>
        <label for="password">Passord:</label>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" value="Logg inn">
      </form>
    </main>

    <footer>
        <!-- Footer kommer her -->
    </footer>

    <script src="js/script.js"></script>
</body>

</html>