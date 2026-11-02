document.querySelectorAll('.fill-test-account').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById('email').value = button.dataset.email;
        document.getElementById('password').value = button.dataset.password;
        document.getElementById('test-account-status').textContent =
            'Testkonto fylt inn. Trykk Logg inn for å fortsette.';
    });
});
