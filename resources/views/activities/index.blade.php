@extends('layouts.app')
@section('title', 'ค้นหากิจกรรม | UniMate')

@php
    // โน้ตตกแต่งใน hero (สไตล์โพสต์อิทของ fastwork) โหลดฟอนต์ลายมือเฉพาะตัวอักษรที่ใช้
    $notes = [
        ["ขาดอีก 2 คน\nไปตีแบดกัน!", 'bg-tint-aqua', 'left-6 top-10 -rotate-6'],
        ["ติวก่อนสอบ\nใครว่างบ้าง?", 'bg-tint-butter', 'left-16 bottom-10 rotate-3'],
        ["หาเพื่อน\nไปปลูกป่าชายเลน", 'bg-tint-mint', 'right-8 top-12 rotate-6'],
        ["แจมดนตรีเย็นนี้\nใครเล่นกีตาร์ได้?", 'bg-tint-lilac', 'right-20 bottom-8 -rotate-3'],
    ];
    $advancedOpen = request()->filled('location') || request()->filled('date') || request('status', 'published') !== 'published';
    $hasFilters = $advancedOpen || request()->filled('q') || request()->filled('category_id');
    $chipUrl = fn ($categoryId) => route('activities.index', array_filter([...request()->except(['page', 'category_id']), 'category_id' => $categoryId]));
@endphp

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Playpen+Sans+Thai:wght@500&text={{ urlencode(implode('', array_column($notes, 0))) }}&display=swap" rel="stylesheet">
@endsection

@section('content')
<section class="relative overflow-hidden rounded-card border border-line/80 bg-white px-5 py-8 text-center shadow-soft sm:px-10 sm:py-14">
    <div aria-hidden="true" class="pointer-events-none absolute inset-0" style="background: radial-gradient(55% 60% at 8% 0%, rgba(179, 244, 239, .55), transparent 60%), radial-gradient(45% 55% at 96% 8%, rgba(211, 189, 255, .45), transparent 60%), radial-gradient(60% 50% at 50% 120%, rgba(255, 226, 153, .45), transparent 65%);"></div>
    @foreach($notes as [$text, $color, $position])
        <span aria-hidden="true" class="pointer-events-none absolute hidden w-40 whitespace-pre-line p-3 text-left font-note text-[15px] leading-snug text-ink-soft shadow-lift xl:block {{ $color }} {{ $position }}">{{ $text }}</span>
    @endforeach

    <div class="relative mx-auto max-w-2xl">
        <span class="kicker mb-5 hidden sm:inline-flex"><x-ui.icon name="sparkles" class="h-4 w-4 text-brand-600" /> ระบบจับกลุ่มทำกิจกรรมในมหาวิทยาลัย</span>
        <h1 class="text-[34px] font-medium leading-[1.15] tracking-tight text-ink-soft sm:text-5xl">หาเพื่อน<span class="text-gradient">ร่วมกิจกรรม</span></h1>
        <p class="mt-3 text-balance text-ink-muted">ค้นหากิจกรรมที่สนใจ แล้วดู<span class="whitespace-nowrap">รายละเอียดการนัดหมาย</span></p>

        <form method="GET" action="{{ route('activities.index') }}" class="mt-7" role="search">
            @if(request()->filled('category_id'))
                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            @endif
            <div class="flex items-center gap-2 rounded-full border border-line-strong bg-white p-1.5 pl-5 shadow-soft transition focus-within:border-brand-600 focus-within:ring-4 focus-within:ring-brand-600/15">
                <x-ui.icon name="search" class="text-ink-faint" />
                <label for="q" class="sr-only">คำค้น</label>
                <input id="q" name="q" value="{{ request('q') }}" placeholder="ชื่อหรือรายละเอียดกิจกรรม" maxlength="200"
                       class="h-11 min-w-0 flex-1 bg-transparent text-[15px] text-ink outline-none placeholder:text-ink-faint focus:ring-0">
                <button class="btn btn-primary">ค้นหา</button>
            </div>

            <details class="group mt-4 text-left" @if($advancedOpen) open @endif>
                <summary class="mx-auto flex w-fit cursor-pointer items-center gap-2 rounded-full px-3 py-1.5 text-sm text-ink-muted transition hover:bg-canvas hover:text-ink">
                    <x-ui.icon name="filters" class="h-4 w-4" />
                    ตัวกรองเพิ่มเติม (สถานที่ · วันที่ · สถานะ)
                    <x-ui.icon name="chevron-down" class="h-4 w-4 transition group-open:rotate-180" />
                </summary>
                <div class="mt-3 grid gap-4 rounded-2xl border border-line bg-canvas/60 p-4 sm:grid-cols-3">
                    <div>
                        <label for="location" class="field-label">สถานที่</label>
                        <input id="location" name="location" value="{{ request('location') }}" class="field" placeholder="เช่น สนามกีฬา" maxlength="255">
                    </div>
                    <div>
                        <label for="date" class="field-label">วันที่เริ่มกิจกรรม</label>
                        <input id="date" type="date" name="date" value="{{ request('date') }}" class="field">
                    </div>
                    <div>
                        <label for="status" class="field-label">สถานะ</label>
                        <select id="status" name="status" class="field">
                            <option value="published" @selected(request('status', 'published') === 'published')>ประกาศแล้ว</option>
                            <option value="cancelled" @selected(request('status') === 'cancelled')>ยกเลิกแล้ว</option>
                            <option value="all" @selected(request('status') === 'all')>ทั้งหมด</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 sm:col-span-3">
                        <a href="{{ route('activities.index') }}" class="btn btn-ghost btn-sm">ล้างตัวกรอง</a>
                        <button class="btn btn-primary btn-sm">ใช้ตัวกรอง</button>
                    </div>
                </div>
            </details>
        </form>

        <nav class="-mx-5 mt-6 flex gap-2 overflow-x-auto px-5 pb-1 [scrollbar-width:none] sm:mx-0 sm:flex-wrap sm:justify-center sm:overflow-visible sm:px-0" aria-label="กรองตามหมวดหมู่">
            <a href="{{ $chipUrl(null) }}" @class(['chip h-9 shrink-0 px-4 text-sm transition', 'chip-dark' => ! request()->filled('category_id'), 'chip-outline hover:border-ink-faint' => request()->filled('category_id')])>ทุกหมวดหมู่</a>
            @foreach($categories as $category)
                @php $active = (string) request('category_id') === (string) $category->id; @endphp
                <a href="{{ $chipUrl($category->id) }}" @if($active) aria-current="true" @endif @class(['chip h-9 shrink-0 px-4 text-sm transition', 'chip-dark' => $active, 'chip-outline hover:border-ink-faint' => ! $active])>{{ $category->name }}</a>
            @endforeach
        </nav>
    </div>
</section>

@include('activities.errors')

<section class="mt-10" aria-labelledby="results-heading">
    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 id="results-heading" class="text-xl font-medium text-ink">{{ $hasFilters ? 'ผลการค้นหา' : 'กิจกรรมทั้งหมด' }}</h2>
            <p class="mt-1 text-sm text-ink-muted">พบ {{ $activities->total() }} กิจกรรม</p>
        </div>
        @if($hasFilters)
            <a href="{{ route('activities.index') }}" class="btn btn-secondary btn-sm"><x-ui.icon name="x" class="h-4 w-4" /> ล้างตัวกรอง</a>
        @endif
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($activities as $activity)
            <x-activity-card :activity="$activity" class="reveal" style="--i: {{ $loop->index }}" />
        @empty
            <div class="col-span-full flex flex-col items-center rounded-card border border-dashed border-line-strong bg-white/60 px-6 py-14 text-center">
                <span class="grid h-14 w-14 place-items-center rounded-2xl bg-canvas text-ink-muted"><x-ui.icon name="search" class="h-6 w-6" /></span>
                <p class="mt-4 font-medium text-ink">ไม่พบกิจกรรมที่ตรงกับตัวกรอง</p>
                <p class="mt-1 text-sm text-ink-muted">ลองเปลี่ยนคำค้นหรือสร้างกิจกรรมใหม่</p>
                <div class="mt-6 flex flex-wrap justify-center gap-2">
                    @if($hasFilters)<a href="{{ route('activities.index') }}" class="btn btn-secondary">ล้างตัวกรอง</a>@endif
                    <a href="{{ route('activities.create') }}" class="btn btn-primary"><x-ui.icon name="plus" class="h-4 w-4" /> สร้างโพสต์กิจกรรม</a>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $activities->links('partials.pagination') }}</div>
</section>
@endsection
