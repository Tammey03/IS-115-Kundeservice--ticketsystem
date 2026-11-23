// Historikkbufferen kan gjenopprette HTML uten å kontakte PHP.
// Last siden på nytt slik at PHP kontrollerer sesjonen igjen.
window.addEventListener("pageshow", (event) => {
    if (event.persisted) {
        window.location.reload();
    }
});
