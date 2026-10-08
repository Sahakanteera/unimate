{{-- ส่วนท้ายเป็นการ์ดสีเข้มมุมโค้ง (ประยุกต์จากการ์ดปิดท้ายของ fastwork) --}}
@guest
<footer class="mt-auto px-4 pb-6 pt-2 text-center text-xs text-ink-muted">
    &copy; 2026 UniMate - ระบบจับกลุ่มหาเพื่อนร่วมทำกิจกรรมในมหาวิทยาลัย
</footer>
@else
<footer class="mt-auto px-3 pb-28 sm:px-4 lg:pb-3">
    <div class="mx-auto max-w-[76rem] rounded-card bg-night px-6 py-8 text-white/70 sm:px-10">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-white text-lg font-semibold text-night">U</span>
                <div>
                    <p class="font-medium text-white">UniMate</p>
                    <p class="text-sm">หาเพื่อนร่วมกิจกรรมในมหาวิทยาลัยได้ในไม่กี่คลิก</p>
                </div>
            </div>
            <nav class="flex flex-wrap gap-x-6 gap-y-2 text-sm" aria-label="ลิงก์ส่วนท้าย">
                <a href="{{ route('activities.index') }}" class="transition hover:text-white">กิจกรรม</a>
                <a href="{{ route('activities.create') }}" class="transition hover:text-white">สร้างโพสต์</a>
                <a href="{{ route('my-activities.index') }}" class="transition hover:text-white">นัดของฉัน</a>
                <a href="{{ route('profile.edit') }}" class="transition hover:text-white">โปรไฟล์</a>
            </nav>
        </div>
        <p class="mt-6 border-t border-white/10 pt-6 text-xs text-white/50">&copy; 2026 UniMate - ระบบจับกลุ่มหาเพื่อนร่วมทำกิจกรรมในมหาวิทยาลัย</p>
    </div>
</footer>
@endguest
