<?php
require_once __DIR__ . '/../../config/auth.php';
requireRoles(['superadmin']);
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin | Kundeservice</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="mainSuperadmin.php" class="logo">Kundeservice</a>
            <div class="nav-links">
                <a href="mainSuperadmin.php">Hjem</a>
                <form class="logout-form" action="../logout.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="logout-button">Logg ut</button>
                </form>
            </div>
        </nav>
    </header>
    <main>
        <section class="hero">
            <h1>Superadmin</h1>
            <p>Overordnet administrasjon av brukere og saker.</p>
        </section>
        <section class="main-content">
            <div class="service-card">
                <h2>Administrere brukere</h2>
                <p>Her kommer oversikt, redigering og sletting av gamle brukere.</p>
                <p>Funksjonen er ikke tilgjengelig ennå.</p>
            </div>
            <div class="service-card">
                <h2>Administrere saker</h2>
                <p>Egen saksadministrasjon for superadmin er ikke tilgjengelig ennå.</p>
            </div>
        </section>
    </main>
    <script src="../../js/navBar.js"></script>
</body>
</html>
