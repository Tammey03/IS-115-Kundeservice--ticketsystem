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

                <a href="../../login.php" class="logout-button">
                    Logg ut
                </a>

            </div>

        </nav>

    </header>

    <!-- =========================
         HOVEDINNHOLD
         ========================= -->
    <main>

        <!-- Introduksjon -->
        <section class="hero">

            <h1>Hvordan kan vi hjelpe deg?</h1>

            <p>
                Opprett en ny sak eller få oversikt over
                henvendelsene dine.
            </p>

        </section>


        <!-- Kortene på hovedsiden -->
        <section class="main-content">


            <!-- Opprett sak -->
            <div class="service-card">

                <h2>Opprett ny sak</h2>

                <p>
                    Her kan du opprette nye saker og sende inn
                    henvendelser til kundeservice.
                </p>

                <a href="opprettSak.php" class="button">
                    Opprett sak
                </a>

            </div>


            <!-- Mine saker -->
            <div class="service-card">

                <h2>Mine saker</h2>

                <p>
                    Her kan du se dine eksisterende saker
                    og deres status.
                </p>

                <a href="mineSaker.php" class="button">
                    Se mine saker
                </a>

            </div>


            <!-- Profil -->
            <div class="service-card">

                <h2>Min profil</h2>

                <p>
                    Her kan du se og redigere
                    profilinformasjonen din.
                </p>

                <a href="minProfil.php" class="button">
                    Se min profil
                </a>

            </div>


            <!-- Chat -->
            <div class="service-card">

                <h2>Chat med kundeservice</h2>

                <p>
                    Chat direkte med kundeservice dersom
                    du trenger hjelp.
                </p>

                <!--
                    chat.php ligger i pages-mappen.
                    Derfor går vi én mappe tilbake.
                -->
                <a href="../chat.php" class="button">
                    Start chat
                </a>

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

</body>

</html>