<?php
require_once __DIR__ . '/../../../config/auth.php';
requireRoles(['user']);
?>
<header class="header">

        <nav class="navbar">

            <!-- Logo / navn på systemet -->
            <a href="mainUser.php" class="logo">
                Kundeservice
            </a>

            <!-- Navigasjon mellom brukerens sider -->
            <div class="nav-links">

                <a href="mainUser.php">Hjem</a>

                <a href="mineSaker.php">Mine saker</a>

                <a href="opprettSak.php">Opprett sak</a>

                <a href="minProfilUser.php">Min profil</a>

                <form class="logout-form" action="../logout.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="logout-button">Logg ut</button>
                </form>

            </div>

        </nav>

    </header>
