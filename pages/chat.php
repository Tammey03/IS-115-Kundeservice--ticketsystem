<?php
/* =========================
   INNLOGGING OG FELLES FILER
   Tillater kunde og admin, og laster database og meldingsfunksjoner.
   ========================= */
require_once __DIR__ . '/../config/auth.php';
requireRoles(['user', 'admin']);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/messages.php';
require_once __DIR__ . '/../includes/conversations.php';
require_once __DIR__ . '/../includes/unreadMessages.php';

/* =========================
   SAKSNUMMER FRA ADRESSEN
   Avviser forespørsler uten et positivt heltall som saksnummer.
   ========================= */
$ticketId = filter_input(
    INPUT_GET,
    'saksnummer',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

// Manglende saksnummer åpner oversikten.
// Et ugyldig saksnummer skal fortsatt avvises.
if (isset($_GET['saksnummer']) && !$ticketId) {
    http_response_code(400);
    exit('Ugyldig saksnummer.');
}
/*
// Sending krever at en sak er valgt. 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$ticketId) {
    http_response_code(400);
    exit('Velg en sak før du sender en melding.');
}
*/

/* =========================
   GENERELL SAMTALE FRA ADRESSEN
   Eksempel: chat.php?samtale=1
   ========================= */
$conversationId = filter_input(
    INPUT_GET,
    'samtale',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (isset($_GET['samtale']) && !$conversationId) {
    http_response_code(400);
    exit('Ugyldig samtalenummer.');
}

// En side skal vise enten en sak eller en generell samtale.
if ($ticketId && $conversationId) {
    http_response_code(400);
    exit('Velg enten en sak eller en generell samtale.');
}

$samtale = null;
$ticket = null;
$meldinger = [];

/* =========================
   SKJEMAVERDIER
   Feilmelding og meldingstekst som kan vises i sendeskjemaet.
   ========================= */
$sendError = '';
$messageText = '';

// Vis ny samtale bare for kunder som har trykket «Ny samtale».
$nySamtale = ($_GET['ny'] ?? null) === '1'
    && $_SESSION['role'] === 'user';
    // Bare én type samtale kan være valgt om gangen.
    if ($nySamtale && ($ticketId || $conversationId)) {
        http_response_code(400);
        exit('Velg enten ny samtale eller en eksisterende samtale.');
    }

    // Sending krever en valgt samtale eller en ny kundesamtale.
    if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && !$ticketId && !$conversationId && !$nySamtale) {
        http_response_code(400);
        exit('Velg en samtale før du sender en melding.');
    }



/* =========================
   HENT SAK OG SAMTALELISTE
   Kontrollerer tilgang til valgt sak og henter tilgjengelige saker.
   ========================= */
try {
   $samtaleSaker = hentSamtaleSaker($pdo);
   $generelleSamtaler = hentMineSamtaler($pdo);
   // Hent generell samtale og historikk etter tilgangskontroll.
    if ($conversationId) {
        $samtale = hentGenerellSamtale($pdo, $conversationId);

        if ($samtale === null) {
            http_response_code(404);
            exit('Samtalen finnes ikke, eller du har ikke tilgang.');
        }

        $meldinger = hentGenerelleMeldinger($pdo, $conversationId);
    }

    if ($ticketId) {
    $ticket = hentSakForSamtale($pdo, $ticketId);

        if ($ticket === null) {
             http_response_code(404);
             exit('Saken finnes ikke, eller du har ikke tilgang.');
         }
    }

    /* =========================
   BEHANDLE INNSENDT MELDING
   Kontrollerer CSRF, lagrer meldingen og videresender etter sending.
   ========================= */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $messageText = is_string($_POST['message'] ?? null)
        ? trim($_POST['message'])
        : '';

    if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $sendError = 'Ugyldig skjema. Last siden på nytt.';
        } else {
            try {
               // Velg riktig lagring for ny samtale, generell samtale eller sak.
                if ($nySamtale) {
                    $newId = opprettSamtale($pdo, $messageText);
                    $destination = 'chat.php?samtale=' . $newId;

                } elseif ($conversationId) {
                    lagreGenerellMelding($pdo, $conversationId, $messageText);
                    $destination = 'chat.php?samtale=' . $conversationId;

                } else {
                    lagreMelding($pdo, $ticketId, $messageText);
                    $destination = 'chat.php?saksnummer=' . $ticketId;
                }

                header('Location: ' . $destination, true, 303);
                exit;
            } catch (InvalidArgumentException $e) {
            $sendError = $e->getMessage();
            } catch (RuntimeException $e) {
                if ($e instanceof PDOException) {
                    throw $e;
                }

                http_response_code(403);
                $sendError = 'Du har ikke tilgang til samtalen.';
            }
        }
    }
   
    // Hent historikken og marker viste meldinger som lest.
    if ($ticketId) {
        $meldinger = hentMeldinger($pdo, $ticketId);

        markerMeldingerSomLest(
            $pdo,
            'ticket',
            $ticketId,
            $meldinger
        );
    }

    if ($conversationId) {
        markerMeldingerSomLest(
            $pdo,
            'conversation',
            $conversationId,
            $meldinger
        );
    }

    // Hent saker og samtaler som fortsatt har uleste meldinger.
    $ulesteSaker = hentUlesteMeldinger($pdo, 'ticket');
    $ulesteSamtaler = hentUlesteMeldinger($pdo, 'conversation');


// Databasefeil logges, mens brukeren får en generell feilmelding.
} catch (PDOException $e) {
    error_log('Kunne ikke hente samtalen: ' . $e->getMessage());
    http_response_code(500);
    exit('Kunne ikke hente saken. Prøv igjen senere.');
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <!-- =========================
         SIDEOPPSETT
         Tittel, stilark og beskyttelse av nettleserhistorikken.
         ========================= -->

    <script src="../js/auth-history.js" defer></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat | Kundeservice</title>
    <link rel="stylesheet" href="../css/style.css">
    <link
    rel="stylesheet"
    href="../css/chat.css?v=<?= filemtime(__DIR__ . '/../css/chat.css') ?>"
>

</head>
<body>
    <!-- =========================
         NAVIGASJON
         Viser adminnavbar eller brukernavbar etter innlogget rolle.
         ========================= -->

    <?php
        if ($_SESSION['role'] === 'admin') {
            require __DIR__ . '/../includes/headerAdmin.php';
        } else {
             require __DIR__ . '/../includes/header.php';
        }       
    ?>
    <main>
        <!-- =========================
         CHATENS HOVEDOPPSETT
         Venstremeny med saker og samtaleområdet til høyre.
         ========================= -->
        <div class="chat-layout">

    <!-- =========================
         VENSTREMENY
         Ny samtale og liste over sakene brukeren har tilgang til.
         ========================= -->
    <aside class="chat-sidebar">
        <h2>Meldinger</h2>

       <!-- Bare kunder kan starte en generell samtale. -->
        <?php if ($_SESSION['role'] === 'user'): ?>
            <a href="chat.php?ny=1" class="button">
                + Ny samtale
            </a>
        <?php endif; ?>
            <!-- =========================
     GENERELLE SAMTALER
     Lagrede samtaler med kundeservice.
     ========================= -->
        <h3>Kundeservice</h3>

        <nav class="chat-case-list" aria-label="Generelle samtaler">
            <?php if (empty($generelleSamtaler)): ?>
                <p>Ingen samtaler ennå.</p>
            <?php endif; ?>

            <?php foreach ($generelleSamtaler as $generell): ?>
                <a
                    href="chat.php?samtale=<?= (int) $generell['conversation_id'] ?>"
                    class="chat-case-link"
                    <?= (int) $generell['conversation_id'] === $conversationId
                        ? 'aria-current="page"'
                        : '' ?>
                >
                    
<strong>
    Samtale #<?= (int) $generell['conversation_id'] ?>

    <?php if (in_array((int) $generell['conversation_id'], $ulesteSamtaler, true)): ?>
        <span class="unread-dot" role="img" aria-label="Uleste meldinger"></span>
    <?php endif; ?>
</strong>


                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <span>
                            <?= htmlspecialchars(
                                $generell['username'],
                                ENT_QUOTES | ENT_SUBSTITUTE,
                                'UTF-8'
                            ) ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <h3>Mine saker</h3>
       <nav class="chat-case-list" aria-label="Sakssamtaler">
    <?php if (empty($samtaleSaker)): ?>
        <p>Ingen saker å vise.</p>
    <?php endif; ?>

    <?php foreach ($samtaleSaker as $sak): ?>
        <a
            href="chat.php?saksnummer=<?= (int) $sak['ticket_id'] ?>"
            class="chat-case-link"
            <?= (int) $sak['ticket_id'] === $ticketId
                ? 'aria-current="page"'
                : '' ?>
        >
            
<strong>
    Sak #<?= (int) $sak['ticket_id'] ?>

   <?php if (in_array((int) $sak['ticket_id'], $ulesteSaker, true)): ?>
    <span class="unread-dot" role="img" aria-label="Uleste meldinger"></span>
<?php endif; ?>
</strong>


            <span>
                <?= htmlspecialchars(
                    $sak['title'],
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) ?>
            </span>
        </a>
    <?php endforeach; ?>
</nav>
    </aside>

    <!-- =========================
         VALGT SAMTALE
         Overskrift, meldingshistorikk og skrivefelt for valgt sak.
         ========================= -->
    <section class="chat-conversation">
       <?php if ($nySamtale): ?>

    <!-- =========================
         NY GENERELL SAMTALE
         Første melding til kundeservice.
         ========================= -->
    <header class="chat-header">
        <h1>Ny samtale med kundeservice</h1>
        <p>Still et generelt spørsmål. Vi svarer så snart vi kan.</p>
    </header>

    <div class="chat-messages">
        <p>Skriv din første melding nedenfor.</p>
    </div>

    <form method="post" action="chat.php?ny=1" class="chat-compose">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"
        >
            <?php if ($sendError !== ''): ?>
                <p role="alert">
                    <?= htmlspecialchars(
                        $sendError,
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    ) ?>
                </p>
            <?php endif; ?>
        <label for="new-message">Din melding</label>

        <textarea
            id="new-message"
            name="message"
            rows="3"
            maxlength="5000"
            placeholder="Hva lurer du på?"
            required
        ></textarea>

        <button type="submit" class="button" >
            Start samtale
        </button>
    </form>

   <?php elseif ($ticket === null && $samtale === null): ?>
        <div class="chat-messages">
            <h1>Meldinger</h1>
            <p>Velg en sak fra listen til venstre for å åpne samtalen.</p>
         </div>
        <?php else: ?>
        <!-- Saksnummer og tittel for den åpne samtalen. -->
    <header class="chat-header">
            <?php if ($samtale !== null): ?>
                <h1>Samtale med kundeservice</h1>
                <p>Generelle spørsmål</p>
            <?php else: ?>
                <h1>Sak #<?= (int) $ticket['ticket_id'] ?></h1>
                <p>
                    <?= htmlspecialchars(
                        $ticket['title'],
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    ) ?>
                </p>
            <?php endif; ?>
    </header>

        <!-- =========================
         MELDINGSHISTORIKK
         Viser avsender, tidspunkt og tekst for hver melding.
         ========================= -->
        <div class="chat-messages">
    <?php if (empty($meldinger)): ?>
        <p>Ingen meldinger ennå.</p>
    <?php else: ?>
        <?php foreach ($meldinger as $melding): ?>
           <article class="chat-message <?= 
                (int) $melding['user_id'] === (int) $_SESSION['user_id']
                ? 'chat-message-own'
                 : 'chat-message-other'
            ?>">
                <strong>
                    <?= htmlspecialchars(
                        $melding['username'],
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    ) ?>
                </strong>

                <time>
                    <?= htmlspecialchars(
                        $melding['created_at'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </time>

                <p class="chat-message-text"><?= htmlspecialchars(
                    $melding['message'],
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) ?></p>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

        <!-- =========================
         SKRIVEFELT OG SEND-KNAPP
         Sender en ny melding i valgt sak.
         ========================= -->
        <form method="post" class="chat-compose">
    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars(
            csrfToken(),
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    >

    <?php if ($sendError !== ''): ?>
        <p role="alert">
            <?= htmlspecialchars(
                $sendError,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            ) ?>
        </p>
    <?php endif; ?>

    <label for="message">Din melding</label>

    <textarea
        id="message"
        name="message"
        rows="3"
        maxlength="5000"
        placeholder="Skriv en melding..."
        required
    ><?= htmlspecialchars(
        $messageText,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    ) ?></textarea>

    <button type="submit" class="button">
        Send
    </button>
</form>
<?php endif; ?>
 </section>

</div>








</main>


</body>

<!-- =========================
         NAVIGASJONSSKRIPT
         Laster JavaScript for navbaren.
         ========================= -->
<script src="../js/navBar.js"></script>
</html>
