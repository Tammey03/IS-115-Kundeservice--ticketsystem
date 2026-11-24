<?php
/*
Filen inneholder felles navigasjon for brukersidene og krever
at brukeren er innlogget med rollen user.

Navigasjonen samles her slik at endringer bare trenger
å gjøres ett sted og blir like på alle brukersidene.

Headeren må inkluderes før innhold sendes til nettleseren,
fordi tilgangskontrollen kan kreve videresending.
*/
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
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
                <?php require __DIR__ . '/logoutForm.php'; ?>


            </div>

        </nav>

    </header>
