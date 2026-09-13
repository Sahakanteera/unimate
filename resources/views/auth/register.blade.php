@extends('layouts.app')

@section('title', 'สมัครสมาชิก - UniMate')

@section('content')
<div class="min-h-[80vh] flex flex-col justify-center py-6 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">
            สมัครสมาชิก <span class="text-blue-600">UniMate</span>
        </h2>
        <p class="mt-2 text-sm text-slate-600">
            สร้างบัญชีนักศึกษาเพื่อเริ่มหาเพื่อนทำกิจกรรมในมหาวิทยาลัย
        </p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-lg">
        <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/50 rounded-2xl border border-slate-100 sm:px-10">
            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700">ชื่อ-นามสกุล <span class="text-rose-500">*</span></label>
                    <input id="name" name="name" type="text" required value="{{ old('name') }}"
                        class="mt-1 w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm transition-all @error('name') border-rose-500 @enderror"
                        placeholder="สมชาย ใจดี">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="student_id" class="block text-sm font-semibold text-slate-700">รหัสนักศึกษา <span class="text-rose-500">*</span></label>
                    <input id="student_id" name="student_id" type="text" required value="{{ old('student_id') }}"
                        class="mt-1 w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm transition-all @error('student_id') border-rose-500 @enderror"
                        placeholder="653020001-1">
                    @error('student_id')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700">อีเมลผู้ใช้ <span class="text-rose-500">*</span></label>
                    <input id="email" name="email" type="email" required value="{{ old('email') }}"
                        class="mt-1 w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm transition-all @error('email') border-rose-500 @enderror"
                        placeholder="student@unimate.ac.th">
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="bio" class="block text-sm font-semibold text-slate-700">คำแนะนำตัว / ข้อมูลส่วนตัว (Bio)</label>
                    <textarea id="bio" name="bio" rows="3"
                        class="mt-1 w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm transition-all"
                        placeholder="เช่น ชอบเล่นบาสเกตบอล ติววิชาคอมพิวเตอร์ ว่างช่วงเย็นวันจันทร์-พุธ">{{ old('bio') }}</textarea>
                </div>

                <div>
                    <div class="flex justify-between items-center">
                        <label for="password" class="block text-sm font-semibold text-slate-700">รหัสผ่าน <span class="text-rose-500">*</span></label>
                        <span class="text-xs text-slate-500 font-medium">(ขั้นต่ำ 6 ตัวอักษร)</span>
                    </div>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" required
                            class="w-full px-4 py-2.5 pr-11 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm transition-all @error('password') border-rose-500 @enderror"
                            placeholder="อย่างน้อย 6 ตัวอักษร">
                        <button type="button" onclick="togglePassword('password', 'eye-reg-pass')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none" title="แสดง/ซ่อนรหัสผ่าน">
                            <svg id="eye-reg-pass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">ยืนยันรหัสผ่าน <span class="text-rose-500">*</span></label>
                    <div class="mt-1 relative">
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            class="w-full px-4 py-2.5 pr-11 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm transition-all"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePassword('password_confirmation', 'eye-reg-confirm')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none" title="แสดง/ซ่อนรหัสผ่าน">
                            <svg id="eye-reg-confirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 px-4 rounded-xl text-white bg-blue-600 hover:bg-blue-700 font-semibold text-sm shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transition-all">
                        ยืนยันการสมัครสมาชิก
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center text-sm text-slate-600">
                มีบัญชีสมาชิกอยู่แล้ว? 
                <a href="{{ route('login') }}" class="font-bold text-blue-600 hover:text-blue-500 ml-1">
                    เข้าสู่ระบบที่นี่
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
