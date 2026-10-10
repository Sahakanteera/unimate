(() => {
    const input = document.getElementById('location_image');
    const preview = document.getElementById('location-image-preview');
    if (!input || !preview) return;
    const savedPhoto = preview.getAttribute('src');
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
})();
