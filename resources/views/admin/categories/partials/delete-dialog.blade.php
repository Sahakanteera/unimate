<dialog id="delete-category-dialog" aria-labelledby="delete-category-title" aria-describedby="delete-category-description" class="m-auto w-[calc(100%_-_2rem)] max-w-sm rounded-card border border-line bg-white p-0 text-ink shadow-lift backdrop:bg-night/40 backdrop:backdrop-blur-sm">
    <div class="p-6">
        <span class="mb-4 grid h-12 w-12 place-items-center rounded-full bg-bad-soft text-bad"><x-ui.icon name="trash" class="h-6 w-6" /></span>
        <h2 id="delete-category-title" class="text-xl font-medium">ลบหมวดหมู่?</h2>
        <p id="delete-category-description" class="mt-2 text-sm leading-relaxed text-ink-muted">ยืนยันลบหมวดหมู่ <strong id="delete-category-name" class="break-anywhere font-medium text-ink"></strong> หรือไม่? เมื่อลบแล้วจะไม่สามารถกู้คืนได้</p>
        <div class="mt-6 flex justify-end gap-2">
            <button id="keep-category" type="button" class="btn btn-secondary" autofocus>เก็บไว้</button>
            <button id="confirm-delete-category" type="button" class="btn border border-bad bg-bad text-white hover:brightness-110">ยืนยันลบ</button>
        </div>
    </div>
</dialog>
