
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
