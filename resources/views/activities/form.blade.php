@extends('layouts.app')
@section('title', ($activity->exists ? 'แก้ไขกิจกรรม' : 'สร้างโพสต์กิจกรรม').' | UniMate')

@php
    // ข้อความ error ใต้ช่องที่ผิด และผูกกับช่องกรอกด้วย aria-describedby ให้โปรแกรมอ่านหน้าจออ่านต่อกัน
    $invalid = fn (string $field) => $errors->has($field) ? 'field-invalid' : '';
    $aria = fn (string $field) => $errors->has($field) ? 'aria-invalid=true aria-describedby='.$field.'-error' : '';
@endphp

@section('content')
<div class="mx-auto max-w-5xl">
    <a href="{{ $activity->exists ? route('activities.show', $activity) : route('activities.index') }}" class="btn btn-ghost btn-sm -ml-3"><x-ui.icon name="arrow-left" class="h-4 w-4" /> กลับหน้ากิจกรรม</a>
    <div class="mb-6 mt-4">
        <h1 class="page-title">{{ $activity->exists ? 'แก้ไขกิจกรรม' : 'สร้างโพสต์หาคนร่วมกิจกรรม' }}</h1>
        <p class="mt-2 text-ink-muted">กรอกรายละเอียดให้ชัดเจน เพื่อให้เพื่อนตัดสินใจเข้าร่วมได้ง่าย</p>
    </div>

    @include('activities.errors')
    @if($categories->isEmpty())<p class="mb-4 flex items-center gap-2 rounded-tile bg-warn-soft p-4 text-warn"><x-ui.icon name="alert" /> ยังไม่มีหมวดหมู่ กรุณาให้ผู้ดูแลระบบเพิ่มหมวดหมู่ก่อนสร้างกิจกรรม</p>@endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
        <form method="POST" action="{{ $activity->exists ? route('activities.update', $activity) : route('activities.store') }}" class="card min-w-0 space-y-8 p-6 sm:p-8">
            @csrf
            @if($activity->exists) @method('PUT') @endif

            <section class="space-y-5">
                <h2 class="section-title flex items-center gap-3"><span class="grid h-7 w-7 place-items-center rounded-full bg-night text-sm text-white">1</span> ข้อมูลกิจกรรม</h2>
                <div>
                    <label for="title" class="field-label">ชื่อกิจกรรม</label>
                    <input id="title" required name="title" maxlength="200" value="{{ old('title', $activity->title) }}" class="field {{ $invalid('title') }}" {{ $aria('title') }} placeholder="เช่น ชวนตีแบดหลังเลิกเรียน">
                    @error('title')<p id="title-error" class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="category_id" class="field-label">หมวดหมู่</label>
                    <select id="category_id" required name="category_id" class="field {{ $invalid('category_id') }}" {{ $aria('category_id') }}><option value="">เลือกหมวดหมู่</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $activity->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select>
                    @error('category_id')<p id="category_id-error" class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description" class="field-label">รายละเอียด</label>
                    <textarea id="description" required name="description" rows="6" maxlength="10000" class="field {{ $invalid('description') }}" {{ $aria('description') }} placeholder="ทำอะไร นัดพบตรงไหน และควรเตรียมอะไรบ้าง">{{ old('description', $activity->description) }}</textarea>
                    @error('description')<p id="description-error" class="field-error">{{ $message }}</p>@enderror
                </div>
            </section>

            <section class="space-y-5 border-t border-line pt-8">
                <h2 class="section-title flex items-center gap-3"><span class="grid h-7 w-7 place-items-center rounded-full bg-night text-sm text-white">2</span> วันเวลาและสถานที่</h2>
                <div>
                    <label for="location" class="field-label">สถานที่</label>
                    <input id="location" required name="location" maxlength="255" value="{{ old('location', $activity->location) }}" class="field {{ $invalid('location') }}" {{ $aria('location') }} placeholder="เช่น โรงยิมเนเซียม 1 คอร์ต 3">
                    @error('location')<p id="location-error" class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="starts_at" class="field-label">เวลาเริ่ม</label>
                        <input id="starts_at" required type="datetime-local" name="starts_at" value="{{ old('starts_at', $activity->starts_at?->format('Y-m-d\TH:i')) }}" class="field {{ $invalid('starts_at') }}" {{ $aria('starts_at') }}>
                        @error('starts_at')<p id="starts_at-error" class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="ends_at" class="field-label">เวลาสิ้นสุด</label>
                        <input id="ends_at" required type="datetime-local" name="ends_at" value="{{ old('ends_at', $activity->ends_at?->format('Y-m-d\TH:i')) }}" class="field {{ $invalid('ends_at') }}" {{ $aria('ends_at') }}>
                        @error('ends_at')<p id="ends_at-error" class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <p class="flex items-start gap-2 text-sm text-ink-muted"><x-ui.icon name="clock" class="mt-0.5 h-4 w-4" /> เวลาไทย (Asia/Bangkok) · เวลาเริ่มต้องอยู่ในอนาคต และเวลาสิ้นสุดต้องอยู่หลังเวลาเริ่ม</p>
            </section>

            <section class="space-y-5 border-t border-line pt-8">
                <h2 class="section-title flex items-center gap-3"><span class="grid h-7 w-7 place-items-center rounded-full bg-night text-sm text-white">3</span> จำนวนคนที่รับ</h2>
                <div class="max-w-xs">
                    <label for="capacity" class="field-label">จำนวนคนที่รับ (ไม่รวมผู้ประกาศ)</label>
                    <input id="capacity" required type="number" min="1" max="10000" name="capacity" value="{{ old('capacity', $activity->capacity) }}" class="field {{ $invalid('capacity') }}" {{ $aria('capacity') }}>
                    @error('capacity')<p id="capacity-error" class="field-error">{{ $message }}</p>@enderror
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:items-center sm:justify-end">
                <a href="{{ $activity->exists ? route('activities.show', $activity) : route('activities.index') }}" class="btn btn-ghost">กลับ</a>
                <button @disabled($categories->isEmpty()) class="btn btn-primary btn-lg">{{ $activity->exists ? 'บันทึกการแก้ไข' : 'ประกาศกิจกรรม' }}</button>
            </div>
        </form>

        <aside class="min-w-0 lg:sticky lg:top-24 lg:self-start">
            <div class="rounded-card bg-tint-butter/60 p-6">
                <p class="flex items-center gap-2 font-medium text-ink"><x-ui.icon name="sparkles" class="h-5 w-5" /> เคล็ดลับโพสต์ให้มีคนอยากร่วม</p>
                <ul class="mt-4 space-y-3 text-sm leading-relaxed text-ink-soft">
                    <li class="flex gap-2"><x-ui.icon name="check" class="mt-0.5 h-4 w-4" /> ตั้งชื่อให้เห็นภาพทันที เช่น กิจกรรม + เวลา</li>
                    <li class="flex gap-2"><x-ui.icon name="check" class="mt-0.5 h-4 w-4" /> บอกจุดนัดพบให้ชัด และสิ่งที่ควรเตรียมมา</li>
                    <li class="flex gap-2"><x-ui.icon name="check" class="mt-0.5 h-4 w-4" /> ระบุระดับฝีมือหรือค่าใช้จ่าย (ถ้ามี)</li>
                </ul>
            </div>
        </aside>
    </div>
</div>
@endsection
