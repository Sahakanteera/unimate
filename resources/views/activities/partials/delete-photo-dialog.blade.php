<dialog id="delete-location-image-dialog" aria-labelledby="delete-photo-title" aria-describedby="delete-photo-description" class="m-auto w-[calc(100%_-_2rem)] max-w-sm rounded-card border border-line bg-white p-0 text-ink shadow-lift backdrop:bg-night/40 backdrop:backdrop-blur-sm">
    <div class="p-6">
        <span class="mb-4 grid h-12 w-12 place-items-center rounded-full bg-bad-soft text-bad"><x-ui.icon name="trash" class="h-6 w-6" /></span>
        <h2 id="delete-photo-title" class="text-xl font-medium">ลบรูปสถานที่?</h2>
        <p id="delete-photo-description" class="mt-2 text-sm leading-relaxed text-ink-muted">ยืนยันลบรูปนี้ออกจากกิจกรรมทันทีหรือไม่? หากต้องการใช้รูปอีกครั้ง จะต้องอัปโหลดใหม่</p>
        <p id="delete-photo-error" class="mt-3 text-sm text-bad" role="alert" hidden></p>
        <div class="mt-6 flex justify-end gap-2">
            <button id="cancel-delete-photo" type="button" class="btn btn-secondary" autofocus>เก็บรูปไว้</button>
            <button id="confirm-delete-photo" type="button" class="btn border border-bad bg-bad text-white hover:brightness-110">ยืนยันลบรูป</button>
        </div>
    </div>
</dialog>
