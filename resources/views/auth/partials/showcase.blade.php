{{-- แผงแบรนด์ของหน้าเข้าสู่ระบบ/สมัครสมาชิก: พื้นเข้ม + โน้ตพาสเทล (ประยุกต์จาก section โพสต์อิทของ fastwork) --}}
@php
    $notes = [
        ["ขาดอีก 2 คน\nตีแบดเย็นนี้ 17:30", 'bg-tint-aqua', '-rotate-6', 'left-[6%] top-[4%]'],
        ["ติวแคลคูลัสก่อนสอบ\nใครว่างบ้าง?", 'bg-tint-butter', 'rotate-3', 'right-[4%] top-[14%]'],
        ["หาเพื่อนไปปลูกป่า\nวันเสาร์นี้ใครว่าง?", 'bg-tint-mint', 'rotate-2', 'left-[10%] top-[44%]'],
        ["แจมดนตรีอะคูสติก\nเอากีตาร์มาด้วย", 'bg-tint-lilac', '-rotate-3', 'right-[8%] top-[56%]'],
    ];
@endphp
<link href="https://fonts.googleapis.com/css2?family=Playpen+Sans+Thai:wght@500&text={{ urlencode(implode('', array_column($notes, 0))) }}&display=swap" rel="stylesheet">
<div class="relative flex h-full min-h-[560px] flex-col justify-between overflow-hidden rounded-card bg-night p-10 text-white">

    <div class="relative">
        <span class="inline-flex h-8 items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3.5 text-[13px] font-medium text-white/80">
            <x-ui.icon name="sparkles" class="h-4 w-4" /> UniMate · สำหรับนักศึกษา
        </span>
        <h2 class="mt-6 text-4xl font-medium leading-tight tracking-tight xl:text-5xl">หาเพื่อนร่วมกิจกรรม<br>ได้ใน<span class="text-gradient-bright">ไม่กี่คลิก</span></h2>
        <p class="mt-4 max-w-md text-white/70">โพสต์ชวนเพื่อนเล่นกีฬา ติวหนังสือ หรือทำจิตอาสา แล้วจับกลุ่มกับนักศึกษาที่สนใจเรื่องเดียวกัน</p>
    </div>

    <div aria-hidden="true" class="relative my-8 h-64">
        @foreach($notes as [$text, $color, $rotate, $position])
            <span class="absolute w-52 whitespace-pre-line p-4 font-note text-base leading-snug text-ink shadow-float {{ $color }} {{ $rotate }} {{ $position }}">{{ $text }}</span>
        @endforeach
    </div>

    <ol class="relative flex flex-wrap gap-2 text-sm">
        @foreach(['ค้นหากิจกรรม', 'ส่งคำขอเข้าร่วม', 'ไปเจอเพื่อนใหม่'] as $step)
            <li class="flex h-9 items-center gap-2 rounded-full bg-white/10 pl-1.5 pr-4 text-white/85">
                <span class="grid h-6 w-6 place-items-center rounded-full bg-white text-xs font-semibold text-night">{{ $loop->iteration }}</span>{{ $step }}
            </li>
        @endforeach
    </ol>
</div>
