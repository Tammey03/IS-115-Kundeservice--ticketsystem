
document.addEventListener("DOMContentLoaded", () => {
    const details = document.getElementById("profile-details");
    const editForm = document.getElementById("profile-edit");
    const editButton = document.getElementById("edit-profile");
    const cancelButton = document.getElementById("cancel-profile");

    if (!details || !editForm || !editButton || !cancelButton) {
        console.error("Fant ikke alle elementene for profilredigering.");
        return;
    }

    editButton.addEventListener("click", () => {
        details.hidden = true;
        editForm.hidden = false;
    });

    cancelButton.addEventListener("click", () => {
        editForm.hidden = true;
        details.hidden = false;
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const passwordDetails = document.getElementById("password-details");
    const passwordEdit = document.getElementById("password-edit");
    const editPassword = document.getElementById("edit-password");
    const cancelPassword = document.getElementById("cancel-password");

    if (!passwordDetails || !passwordEdit ||
        !editPassword || !cancelPassword) {
        return;
    }

    editPassword.addEventListener("click", () => {
        passwordDetails.hidden = true;
        passwordEdit.hidden = false;

        document.getElementById("current_password")?.focus();
    });

    cancelPassword.addEventListener("click", () => {
        passwordEdit.querySelector("form")?.reset();

        passwordEdit.hidden = true;
        passwordDetails.hidden = false;

        editPassword.focus();
    });
});
