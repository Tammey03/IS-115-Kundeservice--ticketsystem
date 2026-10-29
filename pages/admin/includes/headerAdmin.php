<?php
require_once __DIR__ . '/../../../config/auth.php';
requireRoles(['admin']);
?>
<header class="header">

        <nav class="navbar">

            <!-- Logo / navn på systemet -->
            <a href="mainAdmin.php" class="logo">
                Kundeservice
            </a>

            <!-- Navigasjon mellom brukerens sider -->
            <div class="nav-links">

                <a href="mainAdmin.php">Hjem</a>
                <a href="alleSaker.php">Alle saker</a>
                  <a href="behandleSaker.php">Behandle saker</a>
                <a href="minProfilAdmin.php">Min profil</a>

                <form class="logout-form" action="../logout.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="logout-button">Logg ut</button>
                </form>

            </div>

        </nav>

    </header>