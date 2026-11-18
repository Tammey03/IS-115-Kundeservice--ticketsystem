<?php
/*
Filen inneholder felles navigasjon for brukersidene og krever
at brukeren er innlogget med rollen user.

Navigasjonen samles her slik at endringer bare trenger
å gjøres ett sted og blir like på alle brukersidene.

De relative lenkene forutsetter at siden som inkluderer
headeren ligger i pages/bruker.

Headeren må inkluderes før innhold sendes til nettleseren,
fordi tilgangskontrollen kan kreve videresending.
*/
require_once __DIR__ . '/../../../config/auth.php';
// Krever at brukeren er innlogget med rollen user før navigasjonen vises.
requireRoles(['user']);
?>
<header class="header">

        <nav class="navbar">

            <!-- Logo / navn på systemet -->
            <a href="/IS-115-Kundeservice--ticketsystem/pages/bruker/mainUser.php" class="logo">
                Kundeservice
            </a>

            <!-- Navigasjon mellom brukerens sider -->
            <div class="nav-links">

                <a href="/IS-115-Kundeservice--ticketsystem/pages/bruker/mainUser.php">Hjem</a>

                <a href="/IS-115-Kundeservice--ticketsystem/pages/bruker/mineSaker.php">Mine saker</a>

                <a href="/IS-115-Kundeservice--ticketsystem/pages/bruker/opprettSak.php">Opprett sak</a>

                <a href="/IS-115-Kundeservice--ticketsystem/pages/bruker/minProfilUser.php">Min profil</a>

                <!-- Utlogging bruker POST med CSRF-token for å beskytte
                mot at andre nettsider logger ut brukeren uten ønske om det. -->
                <form class="logout-form" action="/IS-115-Kundeservice--ticketsystem/pages/logout.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="logout-button">Logg ut</button>
                </form>

            </div>

        </nav>

    </header>
