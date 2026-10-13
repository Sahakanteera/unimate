(() => {
    const input = document.getElementById('location_image');
    const preview = document.getElementById('location-image-preview');
    if (!input || !preview) return;
    let savedPhoto = preview.getAttribute('src');
    const filename = document.getElementById('location-image-filename');
    let previewUrl;
    input.addEventListener('change', () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        const file = input.files[0];
        if (filename) {
            filename.textContent = file ? file.name : savedPhoto ? 'ใช้รูปสถานที่เดิม' : 'ยังไม่ได้เลือกรูป';
            filename.title = file ? file.name : '';
        }
        previewUrl = file ? URL.createObjectURL(file) : null;
        preview.hidden = !previewUrl && !savedPhoto;
        if (!preview.hidden) preview.src = previewUrl || savedPhoto;
    });
    const deleteButton = document.getElementById('delete-location-image');
    const dialog = document.getElementById('delete-location-image-dialog');
    if (!deleteButton || !dialog) return;

    const confirmButton = document.getElementById('confirm-delete-photo');
    const cancelButton = document.getElementById('cancel-delete-photo');
    const error = document.getElementById('delete-photo-error');
    const status = document.getElementById('location-image-action-status');
    let deleting = false;

    deleteButton.addEventListener('click', () => {
        error.hidden = true;
        dialog.showModal();
    });
    cancelButton.addEventListener('click', () => dialog.close());
    dialog.addEventListener('cancel', (event) => {
        if (deleting) event.preventDefault();
    });
    confirmButton.addEventListener('click', async () => {
        if (deleting) return;
        deleting = true;
        error.hidden = true;
        confirmButton.textContent = 'กำลังลบ…';
        [confirmButton, cancelButton, deleteButton, input].forEach((button) => button.disabled = true);
        try {
            const response = await fetch(deleteButton.dataset.deleteUrl, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': input.closest('form').querySelector('input[name="_token"]').value,
                },
            });
            if (response.redirected || !response.headers.get('content-type')?.includes('application/json')) {
                throw new Error('กรุณาเข้าสู่ระบบใหม่ก่อนลบรูป');
            }
            const data = await response.json();
            if (!response.ok) {
                const messages = {
                    401: 'กรุณาเข้าสู่ระบบใหม่ก่อนลบรูป',
                    403: 'คุณไม่มีสิทธิ์ลบรูปของกิจกรรมนี้',
                    409: data.message || 'ไม่สามารถลบรูปของกิจกรรมนี้ได้',
                    419: 'เซสชันหมดอายุ กรุณาเปิดหน้านี้ใหม่แล้วลองอีกครั้ง',
                };
                throw new Error(messages[response.status] || 'ลบรูปไม่สำเร็จ กรุณาลองใหม่อีกครั้ง');
            }
            savedPhoto = null;
            input.value = '';
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = null;
            preview.hidden = true;
            preview.removeAttribute('src');
            if (filename) {
                filename.textContent = 'ยังไม่ได้เลือกรูป';
                filename.title = '';
            }
            deleteButton.remove();
            status.textContent = 'ลบรูปสถานที่แล้ว';
            status.hidden = false;
            dialog.close();
        } catch (failure) {
            error.textContent = failure instanceof TypeError
                ? 'เชื่อมต่อไม่สำเร็จ กรุณาลองใหม่อีกครั้ง'
                : failure.message;
            error.hidden = false;
        } finally {
            deleting = false;
            confirmButton.textContent = 'ยืนยันลบรูป';
            [confirmButton, cancelButton, deleteButton, input].forEach((button) => button.disabled = false);
        }
    });
})();
