<?php
// Kopier denne filen til config/database.php og fyll inn lokale verdier.
// database.php ignoreres av Git og skal ikke deles med gruppen.
// Databasen og tabellene må opprettes separat før innlogging kan brukes.

$host = "localhost";
$dbname = "kundeservice_db";
$databaseUsername = "root";
$databasePassword = ""; // Standard lokal XAMPP-konfigurasjon. Bruk ditt eget passord ved behov.
$port = 3306;

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $databaseUsername,
        $databasePassword
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Kunne ikke koble til databasen.");
}
