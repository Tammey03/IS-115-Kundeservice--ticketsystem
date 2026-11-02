<?php
require_once __DIR__ . '/../config/auth.php';

$error = '';
$email = '';

// Private testkontoer lastes bare ved lokale forespørsler i utvikling.
$testAccounts = [];
$environment = getenv('APP_ENV');
$isLocal = in_array($_SERVER['SERVER_ADDR'] ?? '', ['127.0.0.1', '::1'], true)
    && in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
$testAccountsFile = __DIR__ . '/../config/test-accounts.php';
if ($isLocal && ($environment === false || $environment === 'development') && is_file($testAccountsFile)) {
    $testAccounts = require $testAccountsFile;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Skjemaet er utløpt. Prøv igjen.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Feil e-post eller passord.';
    } else {
        require_once __DIR__ . '/../config/database.php';
        try {
            $statement = $pdo->prepare(
                'SELECT user_id, username, password_hash, role FROM users WHERE email = :email LIMIT 1'
            );
            $statement->execute(['email' => $email]);
            $user = $statement->fetch();
            // En gyldig reservehash gjør at også ukjent e-post får en passordkontroll.
            $hash = $user ? $user['password_hash'] : '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
            $passwordMatches = password_verify($password, $hash);

            if ($user && $passwordMatches && in_array($user['role'], ['user', 'admin', 'superadmin'], true)) {
                session_regenerate_id(true);
                $_SESSION = [
                    'user_id' => $user['user_id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                ];
                $destinations = [
                    'user' => 'bruker/mainUser.php',
                    'admin' => 'admin/mainAdmin.php',
                    'superadmin' => 'superadmin/mainSuperadmin.php',
                ];
                $destination = $destinations[$user['role']];
                header('Location: ' . $destination);
                exit;
            }
            $error = 'Feil e-post eller passord.';
        } catch (PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            $error = 'Innlogging er midlertidig utilgjengelig. Prøv igjen senere.';
        }
    }
}
?>
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

        <?php if ($error !== ''): ?>
          <div class="login-message login-message-error" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
          </div>
        <?php endif; ?>

        <form class="login-form" action="login.php" method="post">
          <div class="login-field">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <label for="email">E-post</label>
            <input type="email" id="email" name="email" autocomplete="username" value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
          </div>

          <div class="login-field">
            <label for="password">Passord</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required>
          </div>

          <a class="forgot-password" href="glemtPassord.php">Glemt passord?</a>

          <button type="submit">Logg inn</button>
        </form>

        <p class="login-register-link">
          Ny bruker? <a href="opprettBruker.php">Opprett bruker</a>
        </p>
      </section>



      <?php if ($testAccounts): ?>
      <aside class="test-accounts" aria-labelledby="test-accounts-title">
        <h2 id="test-accounts-title">Lokale testkontoer</h2>
        <dl>
          <?php foreach ($testAccounts as $account): ?>
          <dt><?= htmlspecialchars($account['label'], ENT_QUOTES, 'UTF-8') ?></dt>
          <dd>E-post: <code><?= htmlspecialchars($account['email'], ENT_QUOTES, 'UTF-8') ?></code><br>
            Passord: <code><?= htmlspecialchars($account['password'], ENT_QUOTES, 'UTF-8') ?></code><br>
            <button type="button" class="fill-test-account"
                data-email="<?= htmlspecialchars($account['email'], ENT_QUOTES, 'UTF-8') ?>"
                data-password="<?= htmlspecialchars($account['password'], ENT_QUOTES, 'UTF-8') ?>">Fyll inn</button>
          </dd>
          <?php endforeach; ?>
        </dl>
        <p id="test-account-status" role="status"></p>
      </aside>
      <script src="../js/login-test-accounts.js" defer></script>
      <?php endif; ?>
    </main>

    <script src="../js/script.js"></script>
</body>

</html>

