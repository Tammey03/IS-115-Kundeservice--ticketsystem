<?php
require_once __DIR__ . '/../config/auth.php';
requireRoles(['user']);
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat | Kundeservice</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main>
        <h1>Chat med kundeservice</h1>
        <p>Chat er ikke tilgjengelig ennå.</p>
        <a href="bruker/mainUser.php">Tilbake til brukerområdet</a>
    </main>
</body>
</html>
