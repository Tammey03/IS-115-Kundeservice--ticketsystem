<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kundeservice</title>

    <link rel="stylesheet" href="../../css/mainUser.css">
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

                <a href="minProfil.php">Min profil</a>

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

        <form class="ticket-form">

            <div class="form-group">
                <label for="title">Tittel</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    placeholder="Kort beskrivelse av problemet"
                >
            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="category">Kategori</label>

                    <select id="category" name="category">
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

    </section>


</main>

    <footer>
        <!-- Footer kommer her -->
    </footer>

    <script src="js/script.js"></script>
</body>

</html>