<?php
require_once __DIR__ . '/../../config/auth.php';
requireRoles(['admin']);
?>
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
    
    <!-- =========================
         NAVIGASJON
         ========================= -->
 <?php require __DIR__ . '/includes/headerAdmin.php'; ?>

    <main>
        <!-- =========================
             HOVEDINNHOLD
             ========================= -->
             <!-- Introduksjon -->
        <section class="hero">
            <h1>Velkommen til Kundeservice</h1>
            <p>
             Her kan du se alle sakkene som er sendt inn av brukere, og administrere dem etter behov. Du kan også oppdatere din egen profilinformasjon.
            </p>

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
        </section>
    </main>
    <footer>
        <!-- Footer kommer her -->
    </footer>

    <script src="../../js/navBar.js"></script>
</body>

</html>