{{--
    แผงแบรนด์ของหน้าเข้าสู่ระบบ/สมัครสมาชิก: บอร์ดโน้ตชวนเพื่อน (แนวคิดหลักของ UniMate)
    โน้ตเป็นข้อความ HTML แต่ละใบติดไอคอนกระดาษของหมวดไว้ที่มุม ไม่ใช้ภาพถ่ายคนที่ AI สร้าง
--}}
@php
    $notes = [
        ["ขาดอีก 2 คน\nตีแบดเย็นนี้ 17:30", 'bg-tint-aqua', '-rotate-6', 'left-[4%] top-[2%]', 'กีฬา'],
        ["ติวแคลคูลัสก่อนสอบ\nใครว่างบ้าง?", 'bg-tint-butter', 'rotate-3', 'right-[3%] top-[16%]', 'ติวหนังสือ'],
        ["หาเพื่อนไปปลูกป่า\nวันเสาร์นี้ใครว่าง?", 'bg-tint-mint', 'rotate-2', 'left-[9%] top-[50%]', 'จิตอาสา'],
        ["แจมดนตรีอะคูสติก\nเอากีตาร์มาด้วย", 'bg-tint-lilac', '-rotate-3', 'right-[7%] top-[64%]', 'ดนตรี'],
    ];
@endphp
<link href="https://fonts.googleapis.com/css2?family=Playpen+Sans+Thai:wght@500&text={{ urlencode(implode('', array_column($notes, 0))) }}&display=swap" rel="stylesheet">

{{-- มือถือ: แถบสั้นเหนือฟอร์ม ให้เห็นภาพแต่ฟอร์มยังอยู่ในจอแรก --}}
<div aria-hidden="true" class="flex h-32 items-center justify-center gap-4 overflow-hidden rounded-card bg-night sm:h-36 sm:gap-6 lg:hidden">
    @foreach(array_slice($notes, 0, 3) as [, $color, $rotate, , $category])
        <span class="grid h-20 w-20 place-items-center shadow-float sm:h-24 sm:w-24 {{ $color }} {{ $rotate }}">
            <img src="{{ asset(App\Support\Ui::categoryIcon($category, 192)) }}" alt="" width="192" height="192" class="h-14 w-14 sm:h-16 sm:w-16" decoding="async">
        </span>
    @endforeach
</div>

<div class="relative hidden h-full min-h-[600px] flex-col justify-between overflow-hidden rounded-card bg-night p-10 text-white lg:flex">
    <div class="relative">
        <span class="inline-flex items-center gap-2 text-sm font-medium text-white/80">
            <x-ui.brand-mark class="h-8 w-8" /> UniMate · สำหรับนักศึกษา
        </span>
        <h2 class="mt-6 text-4xl font-medium leading-tight tracking-tight xl:text-5xl">หาเพื่อนร่วมกิจกรรม<br>ได้ใน<span class="text-gradient-bright">ไม่กี่คลิก</span></h2>
        <p class="mt-4 max-w-md text-white/70">โพสต์ชวนเพื่อนเล่นกีฬา ติวหนังสือ หรือทำจิตอาสา แล้วจับกลุ่มกับนักศึกษาที่สนใจเรื่องเดียวกัน</p>
    </div>

    <div aria-hidden="true" class="relative my-8 h-72">
        @foreach($notes as [$text, $color, $rotate, $position, $category])
            <span class="absolute w-52 whitespace-pre-line p-4 pr-10 font-note text-base leading-snug text-ink shadow-float {{ $color }} {{ $rotate }} {{ $position }}">{{ $text }}<img src="{{ asset(App\Support\Ui::categoryIcon($category, 192)) }}" alt="" width="192" height="192" class="absolute -right-5 -top-6 h-16 w-16 rotate-6" decoding="async"></span>
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
