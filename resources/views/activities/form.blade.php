@extends('layouts.app')
@section('title', ($activity->exists ? 'แก้ไขกิจกรรม' : 'สร้างโพสต์กิจกรรม').' | UniMate')

@php
    // ข้อความ error ใต้ช่องที่ผิด และผูกกับช่องกรอกด้วย aria-describedby ให้โปรแกรมอ่านหน้าจออ่านต่อกัน
    $invalid = fn (string $field) => $errors->has($field) ? 'field-invalid' : '';
    $aria = fn (string $field) => $errors->has($field) ? 'aria-invalid=true aria-describedby='.$field.'-error' : '';
    $defaultStart = now('Asia/Bangkok')->addHour()->startOfMinute();
    $defaultEnd = $defaultStart->copy()->addHour();
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
        <form method="POST" enctype="multipart/form-data" action="{{ $activity->exists ? route('activities.update', $activity) : route('activities.store') }}" class="card min-w-0 space-y-8 p-6 sm:p-8">
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
                    <label for="location" class="field-label">ชื่อสถานที่ / จุดนัดพบ</label>
                    <input id="location" required name="location" maxlength="255" value="{{ old('location', $activity->location) }}" class="field {{ $invalid('location') }}" {{ $aria('location') }} placeholder="เช่น โรงยิมเนเซียม 1 คอร์ต 3">
                    @error('location')<p id="location-error" class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="location_image" class="field-label">รูปสถานที่ (ไม่บังคับ)</label>
                    <div class="relative flex min-h-[52px] min-w-0 items-center gap-3 rounded-tile border border-line-strong bg-white p-2 transition hover:border-ink-faint focus-within:border-brand-600 focus-within:ring-4 focus-within:ring-brand-600/15 {{ $invalid('location_image') }}">
                        <input id="location_image" type="file" name="location_image" accept="image/jpeg,image/png,image/webp" class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0" aria-describedby="location-image-help{{ $errors->has('location_image') ? ' location_image-error' : '' }}" @if($errors->has('location_image')) aria-invalid="true" @endif>
                        <span class="btn btn-primary btn-sm pointer-events-none shrink-0"><x-ui.icon name="photo" class="h-4 w-4" /> เลือกรูป</span>
                        <span id="location-image-filename" class="pointer-events-none min-w-0 flex-1 truncate text-sm text-ink-muted" role="status" aria-live="polite">{{ $activity->location_image_path ? 'ใช้รูปสถานที่เดิม' : 'ยังไม่ได้เลือกรูป' }}</span>
                    </div>
                    <p id="location-image-help" class="mt-2 text-sm text-ink-muted">JPG, PNG หรือ WebP ขนาดไม่เกิน 5 MB</p>
                    @error('location_image')<p id="location_image-error" class="field-error">{{ $message }}</p>@enderror
                    <img id="location-image-preview" @if($activity->location_image_path) src="{{ asset('storage/'.$activity->location_image_path) }}" @else hidden @endif alt="ตัวอย่างรูปสถานที่" class="mt-3 max-h-64 w-full rounded-tile object-contain">
                    @if($activity->location_image_path)
                        <label class="mt-3 flex items-center gap-2 text-sm"><input type="checkbox" name="remove_location_image" value="1" @checked(old('remove_location_image'))> ลบรูปสถานที่เดิม</label>
                    @endif
                </div>
                <div>
                    <label for="google_maps_url" class="field-label">ลิงก์สถานที่ใน Google Maps (ไม่บังคับ)</label>
                    <input id="google_maps_url" name="google_maps_url" type="url" maxlength="2048" value="{{ old('google_maps_url', $activity->googleMapsUrl()) }}" class="field {{ $invalid('google_maps_url') }}" {{ $aria('google_maps_url') }} placeholder="https://maps.app.goo.gl/...">
                    <p class="mt-2 text-sm text-ink-muted">เปิดสถานที่ใน Google Maps → กดแชร์ → คัดลอกลิงก์ → วางที่นี่ เพื่อให้ผู้เข้าร่วมเปิดดูจุดนัดพบได้</p>
                    @error('google_maps_url')<p id="google_maps_url-error" class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    @include('activities.partials.datetime-field', ['field' => 'starts_at', 'label' => 'เวลาเริ่ม', 'default' => $defaultStart])
                    @include('activities.partials.datetime-field', ['field' => 'ends_at', 'label' => 'เวลาสิ้นสุด', 'default' => $defaultEnd])
                </div>
                <p class="flex items-start gap-2 text-sm text-ink-muted"><x-ui.icon name="clock" class="mt-0.5 h-4 w-4" /> เวลาไทย (Asia/Bangkok) แบบ 24 ชั่วโมง · เวลาเริ่มต้องอยู่ในอนาคต และเวลาสิ้นสุดต้องอยู่หลังเวลาเริ่ม</p>
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
            {{-- บนมือถือแผงนี้อยู่ใต้ปุ่มส่งฟอร์ม จึงแสดงภาพเฉพาะจอใหญ่ที่อยู่ข้างฟอร์ม --}}
            <img src="{{ asset('images/web/illustrations/hero-mates.webp') }}" alt="" width="768" height="512"
                 class="mb-4 hidden h-auto w-full rounded-card bg-white lg:block" loading="lazy" decoding="async">
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

@section('scripts')
    <script src="{{ asset('js/activity-photo.js') }}" defer></script>
@endsection
