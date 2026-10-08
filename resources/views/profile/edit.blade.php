@extends('layouts.app')

@section('title', 'จัดการโปรไฟล์ส่วนตัว - UniMate')

@php
    $hasAvatar = $user->avatar && Storage::disk('public')->exists($user->avatar);
@endphp

@section('content')
<div class="mx-auto max-w-4xl">

    {{-- การ์ดโปรไฟล์ (ประยุกต์จากการ์ดโปรไฟล์ฟรีแลนซ์ของ fastwork) --}}
    <section class="card relative overflow-hidden">
        <div aria-hidden="true" class="h-28 sm:h-32" style="background: radial-gradient(70% 120% at 0% 0%, #b7d4ef, transparent 62%), radial-gradient(60% 120% at 100% 0%, #d5e8e4, transparent 62%), radial-gradient(60% 100% at 50% 100%, #f3e6b8, transparent 70%), #f5f5f3;"></div>
        <div class="px-6 pb-6 sm:px-8 sm:pb-8">
            <div class="-mt-12 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="relative w-fit">
                    <span class="block rounded-full ring-4 ring-white">
                        @if($hasAvatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}?v={{ time() }}" alt="{{ $user->name }}" class="h-24 w-24 rounded-full object-cover">
                        @else
                            <x-ui.avatar :user="$user" size="xl" />
                        @endif
                    </span>
                    <span class="absolute bottom-1.5 right-1.5 h-4 w-4 rounded-full ring-4 ring-white {{ $user->isActive() ? 'bg-emerald-500' : 'bg-bad' }}" title="{{ $user->isActive() ? 'บัญชีปกติ' : 'ถูกระงับ' }}"></span>
                </div>
                @if($user->isAdmin())
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary w-full sm:w-auto"><x-ui.icon name="shield" class="h-4 w-4" /> ไปหน้าแดชบอร์ด Admin</a>
                @endif
            </div>

            <h1 class="break-anywhere mt-4 text-2xl font-medium tracking-tight text-ink-soft sm:text-3xl">{{ $user->name }}</h1>
            <div class="mt-2 flex flex-wrap gap-1.5">
                <span class="chip {{ $user->isAdmin() ? 'chip-warn' : 'chip-info' }}">{{ $user->isAdmin() ? 'ผู้ดูแลระบบ (Admin)' : 'นักศึกษา (Student)' }}</span>
                <span class="chip {{ $user->isActive() ? 'chip-ok' : 'chip-bad' }}">{{ $user->isActive() ? 'ปกติ (Active)' : 'ถูกระงับ (Suspended)' }}</span>
            </div>
            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-ink-muted">
                <span class="flex items-center gap-2"><x-ui.icon name="id-card" class="h-4 w-4" /> รหัสนักศึกษา: <span class="font-medium text-ink-soft">{{ $user->student_id ?? 'N/A (Admin)' }}</span></span>
                <span class="flex items-center gap-2"><x-ui.icon name="mail" class="h-4 w-4" /> <span class="font-medium text-ink-soft">{{ $user->email }}</span></span>
            </div>
            @if($user->bio)
                <p class="break-anywhere mt-4 rounded-tile bg-canvas px-4 py-3 text-sm leading-relaxed text-ink-soft">“{{ $user->bio }}”</p>
            @endif

            <dl class="mt-6 grid grid-cols-2 gap-3 sm:max-w-md">
                <div class="stat flex flex-col-reverse"><dt class="stat-label">กิจกรรมที่สร้าง</dt><dd class="stat-value">{{ $user->activities()->count() }}</dd></div>
                <div class="stat flex flex-col-reverse"><dt class="stat-label">กิจกรรมที่ได้เข้าร่วม</dt><dd class="stat-value">{{ $user->participations()->where('status', 'approved')->count() }}</dd></div>
            </dl>
        </div>
    </section>

    <section class="card mt-6 p-6 sm:p-8">
        <h2 class="flex items-center gap-2 text-xl font-medium text-ink"><x-ui.icon name="pencil" class="text-ink-faint" /> แก้ไขข้อมูลส่วนตัว (Edit Profile)</h2>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-6">
            @csrf

            <input type="hidden" id="avatar_data" name="avatar_data">

            <div class="flex flex-col items-center gap-5 rounded-2xl bg-canvas p-5 sm:flex-row">
                <div class="grid h-20 w-20 shrink-0 place-items-center overflow-hidden rounded-full bg-white text-lg font-semibold text-ink-muted ring-1 ring-line">
                    <img id="avatar-preview" src="{{ $hasAvatar ? asset('storage/' . $user->avatar) : '' }}" class="{{ $hasAvatar ? '' : 'hidden' }} h-full w-full object-cover" alt="ตัวอย่างรูปโปรไฟล์">
                    <span id="avatar-placeholder" class="{{ $hasAvatar ? 'hidden' : '' }}">{{ $user->initials() }}</span>
                </div>

                <div class="flex-1 text-center sm:text-left">
                    <label for="avatar" class="field-label flex items-center justify-center gap-2 sm:justify-start"><x-ui.icon name="photo" class="h-4 w-4" /> รูปโปรไฟล์ (Profile Picture)</label>
                    <input type="file" id="avatar" name="avatar" accept="image/*" onchange="processAvatar(event)"
                        class="block w-full cursor-pointer text-sm text-ink-muted file:mr-4 file:h-10 file:cursor-pointer file:rounded-full file:border-0 file:bg-night file:px-5 file:text-sm file:font-medium file:text-white hover:file:bg-black">
                    <p class="field-hint">เลือกรูปภาพจากเครื่องของคุณ (ระบบจะช่วยปรับขนาดย่อรูปให้ออโต้อัตโนมัติ)</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="name" class="field-label">ชื่อ-นามสกุล</label>
                    <input id="name" name="name" type="text" required value="{{ old('name', $user->name) }}" class="field @error('name') field-invalid @enderror">
                    @error('name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="student_id" class="field-label">รหัสนักศึกษา</label>
                    <input id="student_id" name="student_id" type="text" required value="{{ old('student_id', $user->student_id) }}" class="field @error('student_id') field-invalid @enderror">
                    @error('student_id')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="email" class="field-label">อีเมลผู้ใช้</label>
                <input id="email" name="email" type="email" required value="{{ old('email', $user->email) }}" class="field @error('email') field-invalid @enderror">
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="bio" class="field-label">คำแนะนำตัว / ข้อมูลส่วนตัว (Bio)</label>
                <textarea id="bio" name="bio" rows="3" class="field" placeholder="เล่าข้อมูลเกี่ยวกับตัวคุณ กิจกรรมที่ชอบ หรือเวลาที่สะดวก">{{ old('bio', $user->bio) }}</textarea>
            </div>

            <div class="border-t border-line pt-6">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="flex items-center gap-2 font-medium text-ink"><x-ui.icon name="key" class="h-4 w-4 text-ink-faint" /> เปลี่ยนรหัสผ่าน (หากไม่ต้องการเปลี่ยน ให้เว้นว่างไว้)</h3>
                    <span class="text-xs text-ink-muted">(ขั้นต่ำ 6 ตัวอักษร)</span>
                </div>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="password" class="field-label">รหัสผ่านใหม่ (อย่างน้อย 6 ตัวอักษร)</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" class="field pr-12 @error('password') field-invalid @enderror" placeholder="อย่างน้อย 6 ตัวอักษร">
                            <button type="button" onclick="togglePassword('password', 'eye-edit-pass')" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-tile text-ink-faint hover:text-ink" title="แสดง/ซ่อนรหัสผ่าน" aria-label="แสดง/ซ่อนรหัสผ่าน">
                                <svg id="eye-edit-pass" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="field-label">ยืนยันรหัสผ่านใหม่</label>
                        <div class="relative">
                            <input id="password_confirmation" name="password_confirmation" type="password" class="field pr-12" placeholder="••••••••">
                            <button type="button" onclick="togglePassword('password_confirmation', 'eye-edit-confirm')" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-tile text-ink-faint hover:text-ink" title="แสดง/ซ่อนรหัสผ่าน" aria-label="แสดง/ซ่อนรหัสผ่าน">
                                <svg id="eye-edit-confirm" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end border-t border-line pt-6">
                <button type="submit" class="btn btn-primary btn-lg w-full sm:w-auto">บันทึกการเปลี่ยนแปลง</button>
            </div>
        </form>
    </section>
</div>
@endsection

@section('scripts')
<script>
    function processAvatar(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                const maxDim = 400;
                let width = img.width;
                let height = img.height;

                if (width > height) {
                    if (width > maxDim) {
                        height = Math.round(height * (maxDim / width));
                        width = maxDim;
                    }
                } else {
                    if (height > maxDim) {
                        width = Math.round(width * (maxDim / height));
                        height = maxDim;
                    }
                }

                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                const dataUrl = canvas.toDataURL('image/png', 0.9);

                document.getElementById('avatar_data').value = dataUrl;

                const preview = document.getElementById('avatar-preview');
                const placeholder = document.getElementById('avatar-placeholder');
                preview.src = dataUrl;
                preview.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
</script>
@endsection
