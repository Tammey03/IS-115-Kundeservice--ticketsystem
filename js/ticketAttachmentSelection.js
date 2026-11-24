const attachmentInput = document.getElementById('attachment');
const attachmentList = document.getElementById('attachment-list');
const attachmentCount = document.getElementById('attachment-count');
const maxAttachments = Number(attachmentInput.dataset.maxFiles);
let selectedAttachments = Array.from(attachmentInput.files);

function updateAttachments(message = '') {
    const selection = new DataTransfer();
    selectedAttachments.forEach((file) => selection.items.add(file));
    attachmentInput.files = selection.files;

    attachmentList.replaceChildren();
    attachmentList.hidden = selectedAttachments.length === 0;
    attachmentCount.textContent = message || `${selectedAttachments.length} av ${maxAttachments} filer valgt`;

    selectedAttachments.forEach((file, index) => {
        const row = document.createElement('li');
        const name = document.createElement('span');
        name.textContent = file.name;

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.textContent = 'Fjern';
        remove.setAttribute('aria-label', `Fjern ${file.name}`);
        remove.addEventListener('click', () => {
            selectedAttachments.splice(index, 1);
            updateAttachments();
            const nextRow = attachmentList.children[index] || attachmentList.lastElementChild;
            (nextRow ? nextRow.querySelector('button') : attachmentInput).focus();
        });

        row.append(name, remove);
        attachmentList.append(row);
    });
}

attachmentInput.addEventListener('change', () => {
    const newFiles = Array.from(attachmentInput.files);
    const available = maxAttachments - selectedAttachments.length;
    selectedAttachments = selectedAttachments.concat(newFiles.slice(0, available));
    updateAttachments(newFiles.length > available
        ? `${selectedAttachments.length} av ${maxAttachments} filer valgt. Fjern en fil for å legge til en annen.`
        : '');
});

updateAttachments();
