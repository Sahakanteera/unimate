(() => {
    const dialog = document.getElementById('delete-category-dialog');
    if (!dialog) return;

    const name = document.getElementById('delete-category-name');
    const keepButton = document.getElementById('keep-category');
    const confirmButton = document.getElementById('confirm-delete-category');
    const forms = [...document.querySelectorAll('form[data-delete-category]')];
    let pendingForm = null;
    let confirmedForm = null;
    let submitting = false;

    forms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            const trigger = form.querySelector('button[type="submit"]');
            if (submitting || trigger.disabled) {
                event.preventDefault();
                return;
            }
            if (confirmedForm !== form) {
                event.preventDefault();
                pendingForm = form;
                name.textContent = form.dataset.deleteCategory;
                dialog.showModal();
                return;
            }
            submitting = true;
            trigger.disabled = true;
            keepButton.disabled = true;
            confirmButton.disabled = true;
            confirmButton.textContent = 'กำลังลบ…';
        });
    });

    keepButton.addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => {
        if (!submitting) {
            pendingForm = null;
            confirmedForm = null;
        }
    });
    dialog.addEventListener('cancel', (event) => {
        if (submitting) event.preventDefault();
    });
    confirmButton.addEventListener('click', () => {
        if (!pendingForm || submitting) return;
        confirmedForm = pendingForm;
        pendingForm.requestSubmit();
    });

    window.addEventListener('pageshow', () => {
        if (submitting && pendingForm) pendingForm.querySelector('button[type="submit"]').disabled = false;
        submitting = false;
        pendingForm = null;
        confirmedForm = null;
        keepButton.disabled = false;
        confirmButton.disabled = false;
        confirmButton.textContent = 'ยืนยันลบ';
        if (dialog.open) dialog.close();
    });
})();
