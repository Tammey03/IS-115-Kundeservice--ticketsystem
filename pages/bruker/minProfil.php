<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forside for brukere</title>

    <!--
        ../../ betyr at vi går to mapper tilbake:
        bruker -> pages -> prosjektmappen
        Deretter går vi inn i css-mappen.
    -->
    <link rel="stylesheet" href="../../css/mainUser.css">
    <link rel="stylesheet" href="../../css/minProfil.css">
</head>

<body>

    <!-- =========================
         NAVIGASJON
         ========================= -->
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

                <a href="minProfil.php">Min profil</a>

            </div>

        </nav>

    </header>


    <!-- =========================
         HOVEDINNHOLD
         ========================= -->
   <main>

    <section class="profile-page">

        <div class="profile-title">
            <h1>Min profil</h1>
            <p>Din personlige informasjon</p>
        </div>


        <div class="profile-card">

            <!-- Øvre del -->
            <div class="profile-header">

                <!-- Midlertidig profilbilde -->
                <div class="profile-image">
                    <span>TEST</span>
                </div>

                <div class="profile-name">
                    <h2>Navn på kunde</h2>
                    <p>Kunde</p>
                </div>

            </div>


            <!-- Informasjon -->
            <div class="profile-information">

                <div class="profile-row">
                    <span class="profile-label">Navn</span>
                    <span>Navn på kunde</span>
                </div>

                <div class="profile-row">
                    <span class="profile-label">E-post</span>
                    <span>test@example.no</span>
                </div>

                <div class="profile-row">
                    <span class="profile-label">Telefonnummer</span>
                    <span>12345678</span>
                </div>

                <div class="profile-row">
                    <span class="profile-label">Adresse</span>
                    <span>Eksempelgata 10</span>
                </div>

            </div>

        </div>

    </section>

</main>


    <!-- =========================
         FOOTER
         ========================= -->
    <footer class="footer">

        <p>&copy; 2026 Kundeservice</p>

    </footer>


    <!--
        JavaScript ligger i js-mappen øverst i prosjektet.
        Derfor må vi gå to mapper tilbake.
    -->
    <script src="../../js/navBarUser.js"></script>
    <script src="../../js/minprofil.js"></script>

</body>

</html>