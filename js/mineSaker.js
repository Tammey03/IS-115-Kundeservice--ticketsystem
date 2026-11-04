const sakDialog = document.getElementById('sak-dialog');
const sakDialogContent = document.getElementById('sak-dialog-content');

// Hver knapp peker på malen som inneholder detaljene for akkurat den saken.
document.querySelectorAll('[data-ticket-dialog]').forEach((button) => {
    button.addEventListener('click', () => {
        const template = document.getElementById(button.dataset.ticketDialog);
        sakDialogContent.replaceChildren(template.content.cloneNode(true));
        sakDialog.showModal();
    });
});

// Escape og Lukk-knappen støttes av selve dialog-elementet.
sakDialog.addEventListener('click', (event) => {
    const bounds = sakDialog.getBoundingClientRect();
    if (event.target === sakDialog && (
        event.clientX < bounds.left || event.clientX > bounds.right ||
        event.clientY < bounds.top || event.clientY > bounds.bottom
    )) {
        sakDialog.close();
    }
});
