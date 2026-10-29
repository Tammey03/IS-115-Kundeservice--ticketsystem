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
</head>

<body>
    
   <?php require __DIR__ . '/includes/headerAdmin.php'; ?>

    <main>
     <section class="hero">
            <h1>Velkommen til Kundeservice</h1>
            <p>
                Dette er en kundeserviceplattform som lar deg administrere og spore saker effektivt. 
                Som admin skal du kunne behandle og svare på brukernes saker.
                Saksbehandling er ikke tilgjengelig ennå.
            </p>

            

        </section>
    </main>

    <footer>
        <!-- Footer kommer her -->
    </footer>

    <script src="../../js/navBar.js"></script>
</body>

</html>