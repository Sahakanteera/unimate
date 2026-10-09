{{-- ภาพประกอบบรรยากาศมหาวิทยาลัย: ใช้ crop ต่างกันเพื่อให้เห็นเพื่อนครบทั้งสี่คน --}}
<div class="relative isolate h-40 overflow-hidden rounded-card bg-night sm:h-56 lg:h-[680px]">
    <picture>
        <source media="(max-width: 1023px)" srcset="{{ asset('images/web/photos/auth-campus-friends-mobile.webp') }}">
        <img src="{{ asset('images/web/photos/auth-campus-friends-desktop.webp') }}"
             alt="กลุ่มเพื่อนนักศึกษาในบรรยากาศมหาวิทยาลัย" width="960" height="1200" fetchpriority="high"
             class="absolute inset-0 h-full w-full object-cover object-top">
    </picture>
    <div aria-hidden="true" class="absolute inset-0 hidden bg-gradient-to-b from-night/85 via-transparent to-night/85 lg:block"></div>
    <div aria-hidden="true" class="absolute inset-x-0 top-0 hidden h-[340px] bg-gradient-to-b from-night/90 via-night/80 via-75% to-transparent lg:block"></div>
    <div class="relative hidden h-full flex-col justify-between p-8 text-white lg:flex xl:p-10">
        <div>
            <span class="inline-flex items-center gap-2 text-sm font-medium text-white/90">
                <x-ui.brand-mark class="h-8 w-8" /> UniMate · สำหรับนักศึกษา
            </span>
            <h2 class="mt-5 text-4xl font-medium leading-tight tracking-tight xl:text-[2.75rem]">หาเพื่อนร่วมกิจกรรม<br>ได้ใน<span class="text-gradient-bright">ไม่กี่คลิก</span></h2>
            <p class="mt-3 max-w-sm text-sm leading-relaxed text-white/90">เล่นกีฬา ติวหนังสือ หรือทำจิตอาสา<br>เริ่มจากกิจกรรมที่ชอบ แล้วไปเจอเพื่อนใหม่</p>
        </div>
        <ol class="flex flex-wrap gap-2 text-xs">
            @foreach(['ค้นหากิจกรรม', 'ส่งคำขอเข้าร่วม', 'ไปเจอเพื่อนใหม่'] as $step)
                <li class="flex h-8 items-center gap-2 rounded-full bg-night/70 pl-1 pr-3 text-white">
                    <span class="grid h-6 w-6 place-items-center rounded-full bg-white text-xs font-medium text-night">{{ $loop->iteration }}</span>{{ $step }}
                </li>
            @endforeach
        </ol>
    </div>
</div>
