@extends('layouts.app')

@section('title', 'เข้าสู่ระบบ - UniMate')

@section('content')
<div class="grid gap-8 lg:grid-cols-2 lg:items-stretch">
    <div class="hidden lg:block">@include('auth.partials.showcase')</div>

    <div class="flex items-center justify-center lg:py-10">
        <div class="w-full max-w-md">
            <div class="card p-6 sm:p-10">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-night text-2xl font-semibold text-white">U</span>
                <h1 class="mt-6 text-[1.75rem] font-medium leading-tight tracking-tight text-ink-soft">
                    เข้าสู่ระบบ <span class="text-gradient">UniMate</span>
                </h1>
                <p class="mt-2 text-sm text-ink-muted">ระบบจับกลุ่มหาเพื่อนร่วมทำกิจกรรมในมหาวิทยาลัย</p>

                <form action="{{ route('login') }}" method="POST" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="field-label">อีเมลผู้ใช้</label>
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                            class="field @error('email') field-invalid @enderror" placeholder="student@unimate.ac.th">
                        @error('email')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="field-label">รหัสผ่าน</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" autocomplete="current-password" required class="field pr-12" placeholder="••••••••">
                            <button type="button" onclick="togglePassword('password', 'eye-login')" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-tile text-ink-faint hover:text-ink" title="แสดง/ซ่อนรหัสผ่าน" aria-label="แสดง/ซ่อนรหัสผ่าน">
                                <svg id="eye-login" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-full">เข้าสู่ระบบ</button>
                </form>

                <p class="mt-8 border-t border-line pt-6 text-center text-sm text-ink-muted">
                    ยังไม่มีบัญชีสมาชิก?
                    <a href="{{ route('register') }}" class="link ml-1">สมัครสมาชิกที่นี่</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
