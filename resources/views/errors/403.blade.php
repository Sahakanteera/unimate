@extends('layouts.app')

@section('title', '403 Forbidden - ไม่มีสิทธิ์เข้าถึง')

@section('content')
<div class="min-h-[70vh] flex flex-col items-center justify-center text-center py-12 px-4">
    <div class="w-24 h-24 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-4xl mb-6 shadow-lg shadow-rose-500/10 ring-8 ring-rose-50">
        🔒
    </div>
    
    <span class="px-3 py-1 bg-rose-100 text-rose-800 font-bold text-xs rounded-full border border-rose-200 uppercase tracking-widest mb-3">
        403 ACCESS DENIED
    </span>

    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl">
        ไม่มีสิทธิ์เข้าถึงหน้าผู้ดูแลระบบ
    </h1>
    
    <p class="mt-3 text-base text-slate-600 max-w-md">
        หน้านี้อนุญาตเฉพาะบัญชีผู้ดูแลระบบ <span class="font-semibold text-slate-800">(Admin)</span> เท่านั้น สมาชิกทั่วไปประเภทนักศึกษา <span class="font-semibold text-slate-800">(Student)</span> ไม่สามารถเข้าถึงได้
    </p>

    <div class="mt-8 flex flex-col sm:flex-row gap-3">
        <a href="{{ route('profile.edit') }}" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-lg shadow-blue-500/30 transition-all">
            กลับสู่หน้าโปรไฟล์ของคุณ
        </a>
    </div>
</div>
@endsection
