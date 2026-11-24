<div class="profile-logout">
    <button type="button" class="button" id="open-logout-dialog" aria-haspopup="dialog">Logg ut</button>
</div>

<dialog id="logout-dialog" class="logout-dialog" aria-labelledby="logout-title" aria-describedby="logout-message">
    <h2 id="logout-title">Logge ut?</h2>
    <p id="logout-message">Er du sikker på at du vil logge ut?</p>
    <form class="logout-actions" action="../logout.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="button logout-cancel" formmethod="dialog" autofocus>Avbryt</button>
        <button type="submit" class="button logout-confirm">Logg ut</button>
    </form>
</dialog>

<script src="../../js/logout.js" defer></script>
