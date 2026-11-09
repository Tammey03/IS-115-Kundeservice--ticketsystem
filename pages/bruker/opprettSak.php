<?php
require_once __DIR__ . '/../../config/auth.php';
requireRoles(['user']);
require_once __DIR__ . '/../../classes/Sak.php';

$errors = [];
// Engangsmelding etter videresending gjør at oppdatering ikke sender skjemaet på nytt.
$createdTicketId = $_SESSION['created_ticket_id'] ?? null;
unset($_SESSION['created_ticket_id']);

$title = "";
$category = "";
$priority = "Normal";
$description = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && !validCsrfToken($_POST["csrf_token"] ?? null)) {
    http_response_code(403);
    $errors[] = "Ugyldig skjema. Last siden på nytt og prøv igjen.";
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
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

    // Vedlegg støttes først når sikker fillagring er implementert.
    if (isset($_FILES['attachment']) &&
        ($_FILES['attachment']['error'] ?? null) !== UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Vedlegg kan ikke lagres ennå. Send saken uten vedlegg.';
    }

    // HTML-en nedenfor bruker disse variablene til å vise resultatet.
    $title = $sak->title;
    $category = $sak->category;
    $priority = $sak->priority;
    $description = $sak->description;

    if (empty($errors)) {
        require_once __DIR__ . '/../../config/database.php';
        // Skjemaet viser norske valg, mens databasen bruker engelske verdier.
        $priorities = ['Lav' => 'low', 'Normal' => 'medium', 'Høy' => 'high'];

        try {
            $statement = $pdo->prepare(
                'INSERT INTO tickets (user_id, title, category, priority, description)
                 VALUES (:user_id, :title, :category, :priority, :description)'
            );
            $statement->execute([
                // Eieren hentes fra innloggingen, aldri fra skjemadata.
                'user_id' => $_SESSION['user_id'],
                'title' => $title,
                'category' => $category,
                'priority' => $priorities[$priority],
                'description' => $description,
            ]);

            $_SESSION['created_ticket_id'] = (int) $pdo->lastInsertId();
            header('Location: opprettSak.php', true, 303);
            exit;
        } catch (PDOException $e) {
            error_log('Kunne ikke lagre sak: ' . $e->getMessage());
            http_response_code(500);
            $errors[] = 'Kunne ikke lagre saken. Prøv igjen senere.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="no">

<head>
    <script src="../../js/auth-history.js" defer></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kundeservice</title>

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/oppretteSak.css">
</head>

<body>
    
    <?php require __DIR__ . '/includes/header.php'; ?>

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
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label for="title">Tittel</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    placeholder="Kort beskrivelse av problemet"
                    value="<?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                    maxlength="150"
                   
                >
            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="category">Kategori</label>

                    <select id="category" name="category" >
                        <option value="">Velg kategori</option>
                        <option<?= $category === 'Vann og avløp' ? ' selected' : '' ?>>Vann og avløp</option>
                        <option<?= $category === 'Strøm og varme' ? ' selected' : '' ?>>Strøm og varme</option>
                        <option<?= $category === 'Internett' ? ' selected' : '' ?>>Internett</option>
                        <option<?= $category === 'Skader og vedlikehold' ? ' selected' : '' ?>>Skader og vedlikehold</option>
                        <option<?= $category === 'Nøkler og adgang' ? ' selected' : '' ?>>Nøkler og adgang</option>
                        <option<?= $category === 'Renhold og fellesområder' ? ' selected' : '' ?>>Renhold og fellesområder</option>
                        <option<?= $category === 'Støy og naboforhold' ? ' selected' : '' ?>>Støy og naboforhold</option>
                        <option<?= $category === 'Husleie og kontrakt' ? ' selected' : '' ?>>Husleie og kontrakt</option>
                        <option<?= $category === 'Annet' ? ' selected' : '' ?>>Annet</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Prioritet</label>

                    <select id="priority" name="priority">
                        <option<?= $priority === 'Lav' ? ' selected' : '' ?>>Lav</option>
                        <option<?= $priority === 'Normal' ? ' selected' : '' ?>>Normal</option>
                        <option<?= $priority === 'Høy' ? ' selected' : '' ?>>Høy</option>
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
              
                ><?= htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
            </div>

            <div class="form-group">
                <label for="attachment">Vedlegg</label>

             
                   <input
                        type="file"
                        id="attachment"
                        name="attachment"
                        accept=".pdf,.jpg,.jpeg,.png"
                        disabled
                        aria-describedby="attachment-help"

                    >
                <p id="attachment-help">Vedlegg kan ikke lagres ennå. Send saken uten vedlegg.</p>
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
        <?php if ($createdTicketId !== null): ?>
    <div class="ticket-result" role="status">
        <h2>Saken er lagret</h2>
        <p>Saksnummer: #<?= (int) $createdTicketId ?></p>
        <a href="mineSaker.php" class="details-link">Se mine saker</a>
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
