<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kundeservice</title>

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/mineSaker.css">

</head>

<body>
    
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
                <a href="../login.php" class="logout-button">
                    Logg ut
                </a>

            </div>

        </nav>

    </header>

    

    <main>

        <section class="mine-saker">

            <div class="saker-header">
                <div>
                    <h1><br>Velkommen, [Brukernavn]!</h1>
                    <h1>Mine saker</h1>
                    <p>Her finner du oversikt over sakene du har sendt inn.</p>
                </div>
            </div>


        <a href="opprettSak.php" class="button">
            + Opprett ny sak
        </a>
    </div>


    <div class="table-container">

        <table class="saker-table">

            <thead>
                <tr>
                    <th>Saksnummer</th>
                    <th>Tittel</th>
                    <th>Sendt inn</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                <!-- Mock-data -->
                <tr>
                    <td>#1001</td>
                    <td>Problem med innlogging</td>
                    <td>10.09.2026</td>

                    <td>
                        <span class="status status-apen">
                            Åpen
                        </span>
                    </td>

                    <td>
                        <a href="#" class="details-link">
                            Se sak
                        </a>
                    </td>
                </tr>


                <tr>
                    <td>#1002</td>
                    <td>Problem med betaling</td>
                    <td>08.09.2026</td>

                    <td>
                        <span class="status status-behandles">
                            Under behandling
                        </span>
                    </td>

                    <td>
                        <a href="#" class="details-link">
                            Se sak
                        </a>
                    </td>
                </tr>


                <tr>
                    <td>#1003</td>
                    <td>Endre kontaktinformasjon</td>
                    <td>01.09.2026</td>

                    <td>
                        <span class="status status-lost">
                            Løst
                        </span>
                    </td>

                    <td>
                        <a href="#" class="details-link">
                            Se sak
                        </a>
                    </td>
                </tr>

            </tbody>

        </table>

    </div>



</section>

    </main>


    <footer>
        <!-- Footer kommer her -->
    </footer>

    <script src="../../js/navBar.js"></script>
</body>

</html>