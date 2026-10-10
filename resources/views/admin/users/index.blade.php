@extends('layouts.app')

@section('title', 'จัดการสมาชิกและสิทธิ์ผู้ใช้ (Admin) - UniMate')

@section('content')
<div class="space-y-6">
    @include('admin.partials.header', [
        'title' => 'จัดการบัญชีสมาชิกและระงับสิทธิ์',
        'description' => 'บริหารจัดการผู้ใช้งานในระบบ ตรวจสอบสถานะ และกดระงับบัญชีผู้กระทำผิดกฎ',
    ])

    <dl class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        @foreach([
            ['สมาชิกทั้งหมด', $stats['total'], 'text-ink', 'users', 'bg-tint-aqua'],
            ['นักศึกษา (Students)', $stats['students'], 'text-ink', 'id-card', 'bg-tint-lilac'],
            ['ผู้ดูแลระบบ (Admins)', $stats['admins'], 'text-brand-700', 'shield', 'bg-tint-butter'],
            ['เปิดใช้งาน (Active)', $stats['active'], 'text-ok', 'check-circle', 'bg-tint-mint'],
            ['ถูกระงับ (Suspended)', $stats['suspended'], 'text-bad', 'ban', 'bg-tint-coral'],
        ] as [$label, $value, $color, $icon, $tint])
            <div class="card flex items-center justify-between gap-3 p-5">
                <div>
                    <dt class="text-xs text-ink-muted">{{ $label }}</dt>
                    <dd class="mt-1 text-3xl font-medium tracking-tight tabular-nums {{ $color }}">{{ $value }}</dd>
                </div>
                <span class="hidden h-11 w-11 shrink-0 place-items-center rounded-2xl text-ink sm:grid {{ $tint }}"><x-ui.icon :name="$icon" /></span>
            </div>
        @endforeach
    </dl>

    <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center" role="search">
        <div class="flex flex-1 items-center gap-2 rounded-full border border-line-strong bg-white pl-4 transition focus-within:border-brand-600 focus-within:ring-4 focus-within:ring-brand-600/15">
            <x-ui.icon name="search" class="h-4 w-4 text-ink-faint" />
            <label for="user-search" class="sr-only">ค้นหาสมาชิก</label>
            <input id="user-search" type="text" name="search" value="{{ $search }}" placeholder="ค้นหาด้วยชื่อ, อีเมล หรือรหัสนักศึกษา..."
                class="h-11 min-w-0 flex-1 bg-transparent pr-4 text-base caret-brand-600 outline-none placeholder:text-ink-faint sm:text-[0.9375rem]">
        </div>

        <label for="user-role" class="sr-only">สิทธิ์ผู้ใช้</label>
        <select id="user-role" name="role" onchange="this.form.submit()" class="field h-11 w-full rounded-full sm:w-48">
            <option value="">ทุกสิทธิ์ (All Roles)</option>
            <option value="student" {{ $roleFilter === 'student' ? 'selected' : '' }}>นักศึกษา (Student)</option>
            <option value="admin" {{ $roleFilter === 'admin' ? 'selected' : '' }}>ผู้ดูแลระบบ (Admin)</option>
        </select>

        <label for="user-status" class="sr-only">สถานะบัญชี</label>
        <select id="user-status" name="status" onchange="this.form.submit()" class="field h-11 w-full rounded-full sm:w-52">
            <option value="">ทุกสถานะ (All Status)</option>
            <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>เปิดใช้งานปกติ (Active)</option>
            <option value="suspended" {{ $statusFilter === 'suspended' ? 'selected' : '' }}>ถูกระงับ (Suspended)</option>
        </select>

        <button type="submit" class="btn btn-primary">ค้นหา</button>
        @if($search || $statusFilter || $roleFilter)
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">ล้างตัวกรอง</a>
        @endif
    </form>

    <section class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-line px-6 py-4">
            <h2 class="font-medium text-ink">รายชื่อสมาชิกทั้งหมดในระบบ</h2>
            <span class="text-xs text-ink-muted">แสดง {{ $users->count() }} จาก {{ $users->total() }} บัญชี</span>
        </div>

        <table class="w-full border-collapse text-left text-sm">
            <thead class="hidden bg-canvas/70 text-xs text-ink-muted md:table-header-group">
                <tr>
                    <th class="px-6 py-3 font-medium">นักศึกษา / ผู้ใช้</th>
                    <th class="px-6 py-3 font-medium">รหัสนักศึกษา</th>
                    <th class="px-6 py-3 font-medium">สิทธิ์ผู้ใช้ (Role)</th>
                    <th class="px-6 py-3 font-medium">สถานะ (Status)</th>
                    <th class="px-6 py-3 text-right font-medium">ดำเนินการ (Action)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse($users as $u)
                    <tr class="grid grid-cols-2 gap-x-4 gap-y-3 px-5 py-5 transition hover:bg-canvas/50 md:table-row md:p-0">
                        <td class="col-span-2 md:px-6 md:py-4">
                            <div class="flex items-center gap-3">
                                <x-ui.avatar :user="$u" />
                                <div class="min-w-0">
                                    <div class="break-anywhere font-medium text-ink">{{ $u->name }}</div>
                                    <div class="truncate text-xs text-ink-muted">{{ $u->email }}</div>
                                    @if($u->bio)
                                        <div class="mt-0.5 max-w-xs truncate text-xs italic text-ink-faint" title="{{ $u->bio }}">"{{ $u->bio }}"</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="md:px-6 md:py-4">
                            <span class="block text-xs text-ink-faint md:hidden">รหัสนักศึกษา</span>
                            <span class="font-mono text-xs font-medium text-ink-soft">{{ $u->student_id ?? '-' }}</span>
                        </td>
                        <td class="md:px-6 md:py-4">
                            @if($u->isAdmin())
                                <span class="chip chip-warn h-6 px-2.5 text-xs"><x-ui.icon name="shield" class="h-3.5 w-3.5" /> Admin</span>
                            @else
                                <span class="chip chip-info h-6 px-2.5 text-xs">Student</span>
                            @endif
                        </td>
                        <td class="md:px-6 md:py-4">
                            @if($u->isActive())
                                <span class="chip chip-ok h-6 px-2.5 text-xs"><span class="h-1.5 w-1.5 rounded-full bg-ok"></span> ปกติ (Active)</span>
                            @else
                                <span class="chip chip-bad h-6 px-2.5 text-xs"><span class="h-1.5 w-1.5 rounded-full bg-bad"></span> ถูกระงับ (Suspended)</span>
                            @endif
                        </td>
                        <td class="text-right md:px-6 md:py-4">
                            @if($u->id === Auth::id())
                                <span class="text-xs italic text-ink-faint">(บัญชีของคุณ)</span>
                            @else
                                <div class="inline-flex flex-wrap items-center justify-end gap-1.5">
                                    {{-- ปุ่มสลับสิทธิ์ Admin / Student --}}
                                    <form action="{{ route('admin.users.toggle-role', $u->id) }}" method="POST" class="inline"
                                        onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะ{{ $u->isAdmin() ? 'ลดสิทธิ์เป็นนักศึกษา (Student)' : 'แต่งตั้งเป็นผู้ดูแลระบบ (Admin)' }} สำหรับคุณ {{ $u->name }}?');">
                                        @csrf
                                        @if($u->isAdmin())
                                            <button type="submit" class="btn btn-secondary btn-sm text-xs" title="ลดสิทธิ์เป็นนักศึกษา">
                                                <x-ui.icon name="user" class="h-3.5 w-3.5 text-ink-muted" /> ปลดเป็น Student
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-secondary btn-sm text-xs text-brand-700 hover:border-brand-500 hover:bg-brand-50" title="แต่งตั้งเป็นผู้ดูแลระบบ">
                                                <x-ui.icon name="shield" class="h-3.5 w-3.5 text-brand-600" /> ตั้งเป็น Admin
                                            </button>
                                        @endif
                                    </form>

                                    {{-- ปุ่มระงับ / เปิดใช้งานบัญชี --}}
                                    <form action="{{ route('admin.users.toggle-status', $u->id) }}" method="POST" class="inline"
                                        onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะ {{ $u->isActive() ? 'ระงับบัญชี' : 'เปิดใช้งานบัญชี' }} ของคุณ {{ $u->name }}?');">
                                        @csrf
                                        @if($u->isActive())
                                            <button type="submit" class="btn btn-danger btn-sm text-xs"><x-ui.icon name="ban" class="h-3.5 w-3.5" /> ระงับบัญชี</button>
                                        @else
                                            <button type="submit" class="btn btn-success btn-sm text-xs"><x-ui.icon name="check" class="h-3.5 w-3.5" /> เปิดใช้งาน</button>
                                        @endif
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="block md:table-row">
                        <td colspan="5" class="block px-6 py-12 text-center text-sm text-ink-muted md:table-cell">
                            ไม่พบข้อมูลสมาชิกตามเงื่อนไขการค้นหา
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
            <div class="border-t border-line bg-canvas/50 px-6 py-4">
                {{ $users->links('partials.pagination') }}
            </div>
        @endif
    </section>
</div>
@endsection
