// Delegert klikkhåndtering fungerer også når saksdialogen kopierer inn et galleri.
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-attachment-step], [data-attachment-index]');
    if (!button) {
        return;
    }

    const gallery = button.closest('[data-attachment-gallery]');
    const slides = gallery.querySelectorAll('[data-attachment-slide]');
    const thumbnails = gallery.querySelectorAll('[data-attachment-index]');
    const currentIndex = Array.from(slides).findIndex((slide) => !slide.hidden);
    const nextIndex = button.dataset.attachmentIndex === undefined
        ? (currentIndex + Number(button.dataset.attachmentStep) + slides.length) % slides.length
        : Number(button.dataset.attachmentIndex);

    slides[currentIndex].hidden = true;
    slides[nextIndex].hidden = false;
    thumbnails[currentIndex].setAttribute('aria-current', 'false');
    thumbnails[nextIndex].setAttribute('aria-current', 'true');
    gallery.querySelector('[data-attachment-position]').textContent = nextIndex + 1;
});
