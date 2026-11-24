<?php
require_once __DIR__ . '/../../config/auth.php';
requireRoles(['superadmin']);
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <script src="../../js/auth-history.js" defer></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Min profil | Superadmin</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/minprofil.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="mainSuperadmin.php" class="logo">Kundeservice</a>
            <div class="nav-links">
                <a href="mainSuperadmin.php">Hjem</a>
                <a href="minProfilSuperadmin.php">Min profil</a>
            </div>
        </nav>
    </header>
    <?php require __DIR__ . '/../../includes/logoutForm.php'; ?>
    <main class="profile-page">
        <div class="profile-title">
            <h1>Min profil</h1>
        </div>
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-name">
                    <h2><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p>Superadmin</p>
                </div>
            </div>
        </div>
    </main>
    <script src="../../js/navBar.js"></script>
</body>
</html>
