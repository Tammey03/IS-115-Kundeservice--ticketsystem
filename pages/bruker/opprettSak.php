<?php
$errors = [];
$success = false;

$title = "";
$category = "";
$priority = "";
$description = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    echo "PHP har mottatt skjemaet!";

    // Henter informasjon fra skjemaet
    $title = trim($_POST["title"] ?? "");
    $category = $_POST["category"] ?? "";
    $priority = $_POST["priority"] ?? "";
    $description = trim($_POST["description"] ?? "");

    // Validering av tittel
    if (empty($title)) {
        $errors[] = "Du må fylle ut en tittel.";
    }

    // Validering av kategori
    $validCategories = ["Innlogging", "Betaling", "Teknisk problem", "Annet"];

    if (!in_array($category, $validCategories, true)) {
        $errors[] = "Du må velge en gyldig kategori.";
    }

    // Validering av beskrivelse
    if (empty($description)) {
        $errors[] = "Du må fylle ut en beskrivelse.";
    }

    // Validering av prioritet
    if ($priority === "") {
        $priority = "Normal";
    }

    if (!in_array($priority, ["Lav", "Normal", "Høy"], true)) {
        $errors[] = "Ugyldig prioritet.";
    }

    // Kontroll av om alle feltene er gyldige
    if (empty($errors)) {
        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kundeservice</title>

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/oppretteSak.css">
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
        <!-- =========================
         OPPRETT SAK
         ========================= -->

    <section class="ticket-page">

        <div class="ticket-header">
            <h1>Opprett ny sak</h1>
            <p>
                Fyll ut informasjonen under for å sende inn en ny henvendelse.
            </p>
        </div>
<?php if (!empty($errors)): ?>

    <div class="ticket-errors">
        <h3>Det oppstod noen feil:</h3>

        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

<?php endif; ?>
        <form class="ticket-form" method="POST" action="opprettSak.php" enctype="multipart/form-data">

            <div class="form-group">
                <label for="title">Tittel</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    placeholder="Kort beskrivelse av problemet"
                    required
                >
            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="category">Kategori</label>

                    <select id="category" name="category" required>
                        <option value="">Velg kategori</option>
                        <option>Innlogging</option>
                        <option>Betaling</option>
                        <option>Teknisk problem</option>
                        <option>Annet</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Prioritet</label>

                    <select id="priority" name="priority">
                        <option value="">Velg prioritet</option>
                        <option>Lav</option>
                        <option>Normal</option>
                        <option>Høy</option>
                    </select>
                </div>

            </div>

            <div class="form-group">
                <label for="description">Beskrivelse</label>

                <textarea
                    id="description"
                    name="description"
                    rows="7"
                    placeholder="Beskriv problemet så tydelig som mulig..."
                    required
                ></textarea>
            </div>

            <div class="form-group">
                <label for="attachment">Vedlegg</label>

                <input
                    type="file"
                    id="attachment"
                    name="attachment"
                >
            </div>

            <div class="form-actions">
                <a href="mainUser.php" class="cancel-button">
                    Avbryt
                </a>

                <button type="submit" class="submit-button">
                    Send inn sak
                </button>
            </div>

        </form>
        <?php if ($success): ?>

    <div class="ticket-result">
        <h2>Skjemaet er mottatt!</h2>

        <p><strong>Tittel:</strong>
            <?= htmlspecialchars($title) ?>
        </p>

        <p><strong>Kategori:</strong>
            <?= htmlspecialchars($category) ?>
        </p>

        <p><strong>Prioritet:</strong>
            <?= htmlspecialchars($priority) ?>
        </p>

        <p><strong>Beskrivelse:</strong>
            <?= nl2br(htmlspecialchars($description)) ?>
        </p>
    </div>

<?php endif; ?>

    </section>


</main>

    <footer>
        <!-- Footer kommer her -->
    </footer>

    <script src="../../js/navBar.js"></script>
</body>

</html>
