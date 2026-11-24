<?php
/*
Hensikten med denne filen er for at brukeren kan sendre inn en sak
som det skal sendre videre til sakbehandlen.  
Saksdata valideres med Sak-klassen før lagring i databasen.
Siden krever en innlogget bruker med rollen user.
*/

// Henter og fjerner bekreftelsen fra sesjonen slik at den vises én gang.
require_once __DIR__ . '/../../config/auth.php';
requireRoles(['user']);
require_once __DIR__ . '/../../classes/Sak.php';

$errors = [];
// Engangsmelding etter videresending gjør at oppdatering ikke sender skjemaet på nytt.
$createdTicketId = $_SESSION['created_ticket_id'] ?? null;
$createdAttachmentCount = $_SESSION['created_attachment_count'] ?? 0;
unset($_SESSION['created_ticket_id'], $_SESSION['created_attachment_count']);

$title = "";
$category = "";
$priority = "Normal";
$description = "";

/* Kontrollerer CSRF-token før saksdata behandles for å beskytte 
mot at andre nettsider sender inn skjemaet på brukerens vegne. */

if ($_SERVER["REQUEST_METHOD"] === "POST" && !validCsrfToken($_POST["csrf_token"] ?? null)) {
    http_response_code(403);
    $errors[] = "Ugyldig skjema. Last siden på nytt og prøv igjen.";
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Oppretter en sak med informasjonen fra skjemaet.
    $sak = new Sak(
        $_POST["title"] ?? "",
        $_POST["category"] ?? "",
        $_POST["priority"] ?? "",
        $_POST["description"] ?? ""
    );

    // Klassen kontrollerer feltene og returnerer eventuelle feil.
    $errors = $sak->valider();

    $attachments = [];
    $upload = $_FILES['attachment'] ?? null;
    if ($upload !== null) {
        $uploadErrors = $upload['error'] ?? null;
        if (!is_array($uploadErrors)) {
            $errors[] = 'Ugyldig vedlegg.';
        } elseif (count($uploadErrors) > 5) {
            $errors[] = 'Du kan legge ved maksimalt fem filer.';
        } else {
            foreach ($uploadErrors as $index => $error) {
                $file = [
                    'error' => $error,
                    'name' => $upload['name'][$index] ?? null,
                    'tmp_name' => $upload['tmp_name'][$index] ?? null,
                ];
                $fileErrors = Sak::validerVedlegg($file);
                $errors = array_merge($errors, $fileErrors);
                if (!$fileErrors && $error === UPLOAD_ERR_OK) {
                    $attachments[] = $file;
                }
            }
        }
    }

    // Beholder innholdet i skjemaet ved valideringsfeil,
    // slik at brukeren slipper å fylle ut alt på nytt.
    $title = $sak->title;
    $category = $sak->category;
    $priority = $sak->priority;
    $description = $sak->description;

    if (empty($errors)) {
        require_once __DIR__ . '/../../config/database.php';
        // Skjemaet viser norske valg, mens databasen bruker engelske verdier.
        $priorities = ['Lav' => 'low', 'Normal' => 'medium', 'Høy' => 'high'];

        try {
            $pdo->beginTransaction();
            /*Bruker parametere for å holde skjemadata adskilt fra SQL-koden
            /og beskytte mot SQL-injeksjon.*/
            
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

            $ticketId = (int) $pdo->lastInsertId();
            if ($attachments) {
                // Sett av plass til filnavnet og MySQL-protokollen i samme pakke.
                $packetLimit = (int) $pdo->query('SELECT @@max_allowed_packet')->fetchColumn();
                $statement = $pdo->prepare(
                    'INSERT INTO ticket_attachments (ticket_id, filename, content)
                     VALUES (:ticket_id, :filename, :content)'
                );
                $statement->bindValue(':ticket_id', $ticketId, PDO::PARAM_INT);

                foreach ($attachments as $attachment) {
                    $filename = basename(str_replace('\\', '/', $attachment['name']));
                    $filename = mb_substr(mb_scrub($filename, 'UTF-8'), 0, 255, 'UTF-8');
                    $content = file_get_contents($attachment['tmp_name']);
                    if ($content === false) {
                        throw new RuntimeException('Kunne ikke lese vedlegget.');
                    }

                    if (strlen($content) > $packetLimit - 4096) {
                        $pdo->rollBack();
                        $errors[] = 'Et vedlegg er for stort for databasen. Velg en mindre fil.';
                        break;
                    }

                    $statement->bindValue(':filename', $filename);
                    $statement->bindValue(':content', $content, PDO::PARAM_LOB);
                    $statement->execute();
                }
            }

            if (!$errors) {
                $pdo->commit();
                $_SESSION['created_ticket_id'] = $ticketId;
                $_SESSION['created_attachment_count'] = count($attachments);
                header('Location: opprettSak.php', true, 303);
                exit;
            }
        } catch (RuntimeException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
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
    <script src="../../js/ticketAttachmentSelection.js" defer></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kundeservice</title>

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/oppretteSak.css">
</head>

<body>
    
     <?php require __DIR__ . '/../../includes/header.php'; ?>

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

                 <?php // Bruker htmlspecialchars for å unngå XSS-angrep.  ?>
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
                    name="attachment[]"
                    multiple
                    accept=".pdf,.jpg,.jpeg,.png"
                    aria-describedby="attachment-help"
                    data-max-files="5"
                >
                <p id="attachment-help">Valgfritt. Velg opptil fem PDF-, JPG- eller PNG-filer, opptil 5 MB per fil avhengig av databasegrensen.</p>
                <p id="attachment-count" role="status"></p>
                <ul id="attachment-list" class="attachment-list" hidden></ul>
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
        <?php if ($createdAttachmentCount > 0): ?>
            <p>Antall lagrede vedlegg: <?= (int) $createdAttachmentCount ?></p>
        <?php endif; ?>
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
