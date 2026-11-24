
document.addEventListener("DOMContentLoaded", function () {
    const showPassword = document.getElementById("show-password");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirm_password");

    if (!showPassword || !password || !confirmPassword) {
        return;
    }

    showPassword.addEventListener("change", function () {
        const type = showPassword.checked ? "text" : "password";

        password.type = type;
        confirmPassword.type = type;
    });
});
