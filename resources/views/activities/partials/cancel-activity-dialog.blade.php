<dialog id="cancel-activity-dialog" aria-labelledby="cancel-activity-title" aria-describedby="cancel-activity-description" class="m-auto w-[calc(100%_-_2rem)] max-w-sm rounded-card border border-line bg-white p-0 text-ink shadow-lift backdrop:bg-night/40 backdrop:backdrop-blur-sm">
    <div class="p-6">
        <span class="mb-4 grid h-12 w-12 place-items-center rounded-full bg-bad-soft text-bad"><x-ui.icon name="ban" class="h-6 w-6" /></span>
        <h2 id="cancel-activity-title" class="text-xl font-medium">ยกเลิกกิจกรรม?</h2>
        <p id="cancel-activity-description" class="mt-2 text-sm leading-relaxed text-ink-muted">เมื่อยืนยัน กิจกรรมจะถูกยกเลิกทันทีและไม่สามารถแก้ไขได้</p>
        <div class="mt-6 flex justify-end gap-2">
            <button id="keep-activity" type="button" class="btn btn-secondary" autofocus>กลับ</button>
            <button id="confirm-cancel-activity" type="button" class="btn border border-bad bg-bad text-white hover:brightness-110">ยืนยันยกเลิก</button>
        </div>
    </div>
</dialog>
