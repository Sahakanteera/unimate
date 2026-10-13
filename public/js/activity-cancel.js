(() => {
    const form = document.getElementById('cancel-activity-form');
    const dialog = document.getElementById('cancel-activity-dialog');
    if (!form || !dialog) return;

    const trigger = document.getElementById('open-cancel-activity');
    const keepButton = document.getElementById('keep-activity');
    const confirmButton = document.getElementById('confirm-cancel-activity');
    let confirmed = false;
    let submitting = false;

    form.addEventListener('submit', (event) => {
        if (submitting) {
            event.preventDefault();
            return;
        }
        if (!confirmed) {
            event.preventDefault();
            dialog.showModal();
            return;
        }
        submitting = true;
        trigger.disabled = true;
        keepButton.disabled = true;
        confirmButton.disabled = true;
        confirmButton.textContent = 'กำลังยกเลิก…';
    });

    keepButton.addEventListener('click', () => dialog.close());
    dialog.addEventListener('cancel', (event) => {
        if (submitting) event.preventDefault();
    });
    confirmButton.addEventListener('click', () => {
        if (submitting) return;
        confirmed = true;
        form.requestSubmit();
    });

    window.addEventListener('pageshow', () => {
        confirmed = false;
        submitting = false;
        trigger.disabled = false;
        keepButton.disabled = false;
        confirmButton.disabled = false;
        confirmButton.textContent = 'ยืนยันยกเลิก';
        if (dialog.open) dialog.close();
    });
})();
