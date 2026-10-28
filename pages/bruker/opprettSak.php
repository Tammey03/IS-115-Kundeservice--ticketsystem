<?php
require_once __DIR__ . '/../../classes/Sak.php';

$errors = [];
$success = false;

$title = "";
$category = "";
$priority = "";
$description = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Oppretter en sak med informasjonen fra skjemaet.
    $sak = new Sak(
        $_POST["title"] ?? "",
        $_POST["category"] ?? "",
        $_POST["priority"] ?? "",
        $_POST["description"] ?? "",
        $_FILES["attachment"] ?? null
    );

    // Klassen kontrollerer feltene og returnerer eventuelle feil.
    $errors = $sak->valider();
    $success = empty($errors);

    // HTML-en nedenfor bruker disse variablene til å vise resultatet.
    $title = $sak->title;
    $category = $sak->category;
    $priority = $sak->priority;
    $description = $sak->description;
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
                    maxlength="150"
                   
                >
            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="category">Kategori</label>

                    <select id="category" name="category" >
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
                        <option>Lav</option>
                        <option selected>Normal</option>
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
              
                ></textarea>
            </div>

            <div class="form-group">
                <label for="attachment">Vedlegg</label>

             
                   <input
                        type="file"
                        id="attachment"
                        name="attachment"
                        accept=".pdf,.jpg,.jpeg,.png"

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
        <?php if (
            isset($_FILES['attachment']['error']) &&
            $_FILES['attachment']['error'] === UPLOAD_ERR_OK
             ): ?>
            <p><strong>Vedlegg:</strong> Vedlegget er godkjent.</p>
            <?php else: ?>
            <p><strong>Vedlegg:</strong> Ingen fil valgt.</p>
            <?php endif; ?>
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
