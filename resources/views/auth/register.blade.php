@extends('layouts.app')

@section('title', 'สมัครสมาชิก - UniMate')

@section('content')
<div class="mx-auto grid max-w-xl gap-5 lg:max-w-none lg:grid-cols-2 lg:items-stretch lg:gap-8">
    <div class="lg:sticky lg:top-24 lg:self-start">@include('auth.partials.showcase')</div>

    <div class="flex items-center justify-center lg:py-6">
        <div class="w-full max-w-lg">
            <div class="card p-6 sm:p-10">
                <h1 class="text-[1.75rem] font-medium leading-tight tracking-tight text-ink-soft">
                    สมัครสมาชิก <span class="text-gradient">UniMate</span>
                </h1>
                <p class="mt-2 text-sm text-ink-muted">สร้างบัญชีนักศึกษาเพื่อเริ่มหาเพื่อนทำกิจกรรมในมหาวิทยาลัย</p>

                <form action="{{ route('register') }}" method="POST" class="mt-8 space-y-5">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="field-label">ชื่อ-นามสกุล <span class="text-bad">*</span></label>
                            <input id="name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}"
                                class="field @error('name') field-invalid @enderror" placeholder="สมชาย ใจดี">
                            @error('name')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="student_id" class="field-label">รหัสนักศึกษา <span class="text-bad">*</span></label>
                            <input id="student_id" name="student_id" type="text" required value="{{ old('student_id') }}"
                                class="field @error('student_id') field-invalid @enderror" placeholder="653020001-1">
                            @error('student_id')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="email" class="field-label">อีเมลผู้ใช้ <span class="text-bad">*</span></label>
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                            class="field @error('email') field-invalid @enderror" placeholder="student@unimate.ac.th">
                        @error('email')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bio" class="field-label">คำแนะนำตัว / ข้อมูลส่วนตัว (Bio)</label>
                        <textarea id="bio" name="bio" rows="3" class="field"
                            placeholder="เช่น ชอบเล่นบาสเกตบอล ติววิชาคอมพิวเตอร์ ว่างช่วงเย็นวันจันทร์-พุธ">{{ old('bio') }}</textarea>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <div class="flex items-center justify-between">
                                <label for="password" class="field-label">รหัสผ่าน <span class="text-bad">*</span></label>
                                <span class="mb-1.5 text-xs text-ink-muted">(ขั้นต่ำ 6 ตัวอักษร)</span>
                            </div>
                            <div class="relative">
                                <input id="password" name="password" type="password" autocomplete="new-password" required
                                    class="field pr-12 @error('password') field-invalid @enderror" placeholder="อย่างน้อย 6 ตัวอักษร">
                                <button type="button" onclick="togglePassword('password', 'eye-reg-pass')" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-tile text-ink-faint hover:text-ink" title="แสดง/ซ่อนรหัสผ่าน" aria-label="แสดง/ซ่อนรหัสผ่าน">
                                    <svg id="eye-reg-pass" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <label for="password_confirmation" class="field-label">ยืนยันรหัสผ่าน <span class="text-bad">*</span></label>
                            <div class="relative">
                                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="field pr-12" placeholder="••••••••">
                                <button type="button" onclick="togglePassword('password_confirmation', 'eye-reg-confirm')" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-tile text-ink-faint hover:text-ink" title="แสดง/ซ่อนรหัสผ่าน" aria-label="แสดง/ซ่อนรหัสผ่าน">
                                    <svg id="eye-reg-confirm" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-full">ยืนยันการสมัครสมาชิก</button>
                </form>

                <p class="mt-8 border-t border-line pt-6 text-center text-sm text-ink-muted">
                    มีบัญชีสมาชิกอยู่แล้ว?
                    <a href="{{ route('login') }}" class="link ml-1">เข้าสู่ระบบที่นี่</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
