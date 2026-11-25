
<?php
require_once __DIR__ . '/../../config/auth.php';
requireRoles(['admin']);

$profilePage = 'minProfilAdmin.php';
$profileRoleLabel = 'Saksbehandler';

require_once __DIR__ . '/../../includes/profile.php';
?>
<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Min profil | Admin</title>

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/minprofil.css">

    <script src="../../js/auth-history.js" defer></script>
</head>

<body>

    <?php require __DIR__ . '/../../includes/headerAdmin.php'; ?>

    <?php require_once __DIR__ . '/../../includes/logoutForm.php'; ?>

    <?php require __DIR__ . '/../../includes/profileView.php'; ?>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Kundeservice</p>
    </footer>

    <script src="../../js/navBar.js"></script>
    <script src="../../js/minprofil.js" defer></script>

</body>

</html>
