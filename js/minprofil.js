document.addEventListener("DOMContentLoaded", () => {
    function setupEditor(section) {
        const details = document.getElementById(section + "-details");
        const editForm = document.getElementById(section + "-edit");
        const editButton = document.getElementById("edit-" + section);
        const cancelButton = document.getElementById("cancel-" + section);
        const form = editForm?.querySelector("form");

        if (!details || !form || !editButton || !cancelButton) {
            return;
        }

        editButton.addEventListener("click", (event) => {
            event.preventDefault();
            details.hidden = true;
            editForm.hidden = false;
            form.querySelector("input:not([type='hidden'])")?.focus();
        });

        cancelButton.addEventListener("click", (event) => {
            if (new URLSearchParams(window.location.search).get("edit") === section) {
                return;
            }

            event.preventDefault();
            form.reset();
            editForm.hidden = true;
            details.hidden = false;
            editButton.focus();
        });
    }

    setupEditor("profile");
    setupEditor("password");
});
