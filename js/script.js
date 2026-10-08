document.addEventListener("DOMContentLoaded", () => {

    const form = document.querySelector(".ticket-form");

    if (!form) return;

    const fields = form.querySelectorAll("[required]");

    // Vi bruker vår egen visuelle validering
    form.noValidate = true;

    function validateField(field) {
        const errorMessage =
            field.closest(".form-group").querySelector(".error-message");

        const isValid = field.validity.valid;

        field.classList.toggle("invalid", !isValid);
        errorMessage.classList.toggle("show", !isValid);

        return isValid;
    }

    form.addEventListener("submit", (event) => {

        let formIsValid = true;

        fields.forEach((field) => {
            if (!validateField(field)) {
                formIsValid = false;
            }
        });

        if (!formIsValid) {
            event.preventDefault();
        }
    });

    // Fjern feilmeldingen når feltet blir gyldig
    fields.forEach((field) => {
        field.addEventListener("input", () => {
            validateField(field);
        });

        field.addEventListener("change", () => {
            validateField(field);
        });
    });

});