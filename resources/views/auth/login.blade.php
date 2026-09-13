@extends('layouts.app')

@section('title', 'เข้าสู่ระบบ - UniMate')

@section('content')
<div class="min-h-[75vh] flex flex-col justify-center py-6 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-600 text-white font-extrabold text-3xl shadow-xl shadow-blue-500/30 mb-4">
            U
        </div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">
            เข้าสู่ระบบ <span class="text-blue-600">UniMate</span>
        </h2>
        <p class="mt-2 text-sm text-slate-600">
            ระบบจับกลุ่มหาเพื่อนร่วมทำกิจกรรมในมหาวิทยาลัย
        </p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/50 rounded-2xl border border-slate-100 sm:px-10">
            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700">อีเมลผู้ใช้</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm transition-all @error('email') border-rose-500 @enderror"
                            placeholder="student@unimate.ac.th">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700">รหัสผ่าน</label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" required
                            class="w-full px-4 py-2.5 pr-11 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm transition-all"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePassword('password', 'eye-login')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none" title="แสดง/ซ่อนรหัสผ่าน">
                            <svg id="eye-login" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="submit" class="w-full py-3 px-4 rounded-xl text-white bg-blue-600 hover:bg-blue-700 font-semibold text-sm shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transition-all">
                        เข้าสู่ระบบ
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center text-sm text-slate-600">
                ยังไม่มีบัญชีสมาชิก? 
                <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:text-blue-500 ml-1">
                    สมัครสมาชิกที่นี่
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
