<?php
require_once __DIR__ . '/../../config/auth.php';
requireRoles(['admin']);
?>
<!DOCTYPE html>
<html lang="no">

<head>
    <script src="../../js/auth-history.js" defer></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forside for admin</title>

    <link rel="stylesheet" href="../../css/style.css">
</head>

<body>        
    <!-- =========================
         NAVIGASJON
         ========================= -->
   <?php require __DIR__ . '/../../includes/headerAdmin.php'; ?>
<main>
      <!-- Introduksjon -->
        <section class="hero">

            <?php $firstName = currentUserFirstName(); ?>
            <h1><br>Velkommen<?php if ($firstName !== ''): ?>, <?= htmlspecialchars(
                        $firstName,
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    ) ?><?php endif; ?>!</h1>
                    

            <p>
                Dette er administrasjonspanelet for kundeservice. Her kan du
                se og behandle alle saker som er opprettet av brukere.
            </p>

        </section>


        <!-- Kortene på hovedsiden -->
        <section class="main-content">


            <!-- Opprett sak -->
            <div class="service-card">

                <h2>Se alle saker</h2>

                <p>
                   Her kan du se alle saker som er opprettet av brukere.
                </p>

                <a href="alleSaker.php" class="button">
                    Se alle saker
                </a>

            </div>


       
            <div class="service-card">

                <h2>Behandle saker</h2>

                <p>
                    Her kan du behandle og svare på saker som er opprettet av brukere.
                </p>

                <a href="behandleSaker.php" class="button">
                    Behandle saker
                </a>

            </div>


            <!-- Profil -->
            <div class="service-card">

                <h2>Min profil</h2>

                <p>
                    Her kan du se og redigere
                    profilinformasjonen din.
                </p>

                <a href="minProfilAdmin.php" class="button">
                    Se min profil
                </a>

            </div>

            <!-- =========================
                 MELDINGER
                Samtaler om saker som er tildelt saksbehandleren.
             ========================= -->
            <div class="service-card">
                <h2>Meldinger</h2>

                <p>
                    Les og svar på meldinger fra kunder
                    i sakene du er tildelt.
                </p>

                <a href="../chat.php" class="button">
                     Åpne meldinger
                </a>
            </div>


        </section>

    </main>
   
    <footer>
        <!-- Footer kommer her -->
    </footer>

    <script src="../../js/navBar.js"></script>
</body>

</html>
