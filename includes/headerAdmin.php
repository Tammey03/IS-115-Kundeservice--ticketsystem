
<?php
require_once __DIR__ . '/../config/auth.php';
requireRoles(['admin']);
?>

<header class="header">

    <nav class="navbar">

        <!-- Logo / navn på systemet -->
        <a href="/IS-115-Kundeservice--ticketsystem/pages/admin/mainAdmin.php" class="logo">
            Kundeservice
        </a>

        <!-- Navigasjon mellom administratorens sider -->
        <div class="nav-links">

            <a href="/IS-115-Kundeservice--ticketsystem/pages/admin/mainAdmin.php">
                Hjem
            </a>

            <a href="/IS-115-Kundeservice--ticketsystem/pages/admin/alleSaker.php">
                Saker
            </a>

            <a href="/IS-115-Kundeservice--ticketsystem/pages/admin/behandleSaker.php">
                Behandle saker
            </a>

            <a href="/IS-115-Kundeservice--ticketsystem/pages/admin/fellesSaker.php">
                Felles saker
            </a>

            <a href="/IS-115-Kundeservice--ticketsystem/pages/admin/minProfilAdmin.php">
                Min profil
            </a>

            <!-- Felles utloggingsfunksjonalitet -->
            <?php require_once __DIR__ . '/logoutForm.php'; ?>

        </div>

    </nav>

</header>
