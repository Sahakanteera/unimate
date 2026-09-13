@extends('layouts.app')

@section('title', 'จัดการสมาชิกและสิทธิ์ผู้ใช้ (Admin) - UniMate')

@section('content')
<div class="py-6 max-w-7xl mx-auto space-y-6">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full border border-amber-200">
                    👑 ADMIN PANEL
                </span>
                <h1 class="text-2xl font-bold text-slate-900">จัดการบัญชีสมาชิกและระงับสิทธิ์</h1>
            </div>
            <p class="text-sm text-slate-600 mt-1">
                บริหารจัดการผู้ใช้งานในระบบ ตรวจสอบสถานะ และกดระงับบัญชีผู้กระทำผิดกฎ
            </p>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-md shadow-slate-200/50 border border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">สมาชิกทั้งหมด</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                👥
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-md shadow-slate-200/50 border border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">นักศึกษา (Students)</p>
                <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ $stats['students'] }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                🎓
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-md shadow-slate-200/50 border border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">เปิดใช้งาน (Active)</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['active'] }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                🟢
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-md shadow-slate-200/50 border border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">ถูกระงับ (Suspended)</p>
                <p class="text-2xl font-extrabold text-rose-600 mt-1">{{ $stats['suspended'] }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                🚫
            </div>
        </div>
    </div>

    <div class="bg-white p-4 rounded-2xl shadow-md shadow-slate-200/50 border border-slate-100">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-grow">
                <input type="text" name="search" value="{{ $search }}" placeholder="ค้นหาด้วยชื่อ, อีเมล หรือรหัสนักศึกษา..."
                    class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 text-sm">
            </div>

            <div>
                <select name="status" onchange="this.form.submit()" class="w-full sm:w-auto px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                    <option value="">ทุกสถานะ (All Status)</option>
                    <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>🟢 เปิดใช้งานปกติ (Active)</option>
                    <option value="suspended" {{ $statusFilter === 'suspended' ? 'selected' : '' }}>🔴 ถูกระงับ (Suspended)</option>
                </select>
            </div>

            <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 text-white font-semibold text-sm hover:bg-slate-800 transition-colors">
                ค้นหา
            </button>
            @if($search || $statusFilter)
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-semibold text-sm hover:bg-slate-200 text-center">
                    ล้างตัวกรอง
                </a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">รายชื่อสมาชิกทั้งหมดในระบบ</h2>
            <span class="text-xs font-semibold text-slate-500">แสดง {{ $users->count() }} จาก {{ $users->total() }} บัญชี</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">นักศึกษา / ผู้ใช้</th>
                        <th class="py-3.5 px-6">รหัสนักศึกษา</th>
                        <th class="py-3.5 px-6">สิทธิ์ผู้ใช้ (Role)</th>
                        <th class="py-3.5 px-6">สถานะ (Status)</th>
                        <th class="py-3.5 px-6 text-center">ดำเนินการ (Action)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($users as $u)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-4 px-6">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-200 flex items-center justify-center font-bold text-slate-700 text-xs shadow-sm overflow-hidden flex-shrink-0">
                                    @if($u->avatar && Storage::disk('public')->exists($u->avatar))
                                        <img src="{{ asset('storage/' . $u->avatar) }}" alt="{{ $u->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ $u->initials() }}
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $u->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $u->email }}</div>
                                    @if($u->bio)
                                        <div class="text-xs text-slate-400 italic mt-0.5 max-w-xs truncate">"{{ $u->bio }}"</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="py-4 px-6 font-mono text-xs font-semibold text-slate-700">
                            {{ $u->student_id ?? '-' }}
                        </td>

                        <td class="py-4 px-6">
                            @if($u->isAdmin())
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 inline-flex items-center gap-1">
                                    👑 Admin
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 inline-flex items-center gap-1">
                                    🎓 Student
                                </span>
                            @endif
                        </td>

                        <td class="py-4 px-6">
                            @if($u->isActive())
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> ปกติ (Active)
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 inline-flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> ถูกระงับ (Suspended)
                                </span>
                            @endif
                        </td>

                        <td class="py-4 px-6 text-center">
                            @if($u->id === Auth::id())
                                <span class="text-xs text-slate-400 font-semibold italic">(บัญชีของคุณ)</span>
                            @else
                                <form action="{{ route('admin.users.toggle-status', $u->id) }}" method="POST" class="inline"
                                    onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะ {{ $u->isActive() ? 'ระงับบัญชี' : 'เปิดใช้งานบัญชี' }} ของคุณ {{ $u->name }}?');">
                                    @csrf
                                    @if($u->isActive())
                                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs shadow-md shadow-rose-500/20 transition-all flex items-center gap-1 mx-auto">
                                            🚫 ระงับบัญชี (Suspend)
                                        </button>
                                    @else
                                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-500/20 transition-all flex items-center gap-1 mx-auto">
                                            ✅ เปิดใช้งาน (Activate)
                                        </button>
                                    @endif
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-500 text-sm">
                            ไม่พบข้อมูลสมาชิกตามเงื่อนไขการค้นหา
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
