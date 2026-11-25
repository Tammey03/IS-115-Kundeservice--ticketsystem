<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
?>
<div class="nav-logout">
    <button
        type="button"
        class="nav-logout-button"
        id="open-logout-dialog"
        aria-haspopup="dialog"
    >
        Logg ut
    </button>
</div>

<dialog
    id="logout-dialog"
    class="logout-dialog"
    aria-labelledby="logout-title"
    aria-describedby="logout-message"
>
    <h2 id="logout-title">Logge ut?</h2>

    <p id="logout-message">
        Er du sikker på at du vil logge ut?
    </p>

    <form
        class="logout-actions"
        action="/IS-115-Kundeservice--ticketsystem/pages/logout.php"
        method="post"
    >
        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrfToken()) ?>"
        >

        <button
            type="submit"
            class="button logout-cancel"
            formmethod="dialog"
        >
            Avbryt
        </button>

        <button
            type="submit"
            class="button logout-confirm"
        >
            Logg ut
        </button>
    </form>
</dialog>

<script src="/IS-115-Kundeservice--ticketsystem/js/logout.js" defer></script>
