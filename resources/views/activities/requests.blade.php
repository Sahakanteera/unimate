@extends('layouts.app')
@section('title', 'จัดการคำขอ — '.$activity->title.' | UniMate')

@php
    $approvedTotal = $activity->approvedCount();
    $isFull = $approvedTotal >= $activity->capacity;
    $tabs = [
        'pending' => 'รออนุมัติ',
        'approved' => 'อนุมัติแล้ว',
        'rejected' => 'ปฏิเสธแล้ว',
        'all' => 'ทั้งหมด',
    ];
@endphp

@section('content')
<div class="mx-auto max-w-4xl">
    <a href="{{ route('activities.show', $activity) }}" class="btn btn-ghost btn-sm -ml-3"><x-ui.icon name="arrow-left" class="h-4 w-4" /> กลับหน้ากิจกรรม</a>

    <div class="mt-4">
        <h1 class="page-title">จัดการคำขอเข้าร่วม</h1>
        <p class="break-anywhere mt-2 text-ink-muted">{{ $activity->title }} · {{ $activity->starts_at->copy()->locale('th')->isoFormat('dd D MMM') }} {{ $activity->starts_at->format('H:i') }}</p>
    </div>

    <div class="mt-6 grid grid-cols-3 gap-3">
        <div class="stat bg-white shadow-soft"><p class="stat-value">{{ $counts['pending'] ?? 0 }}</p><p class="stat-label">รออนุมัติ</p></div>
        <div class="stat bg-white shadow-soft"><p class="stat-value">{{ $approvedTotal }}<span class="text-base text-ink-faint">/{{ $activity->capacity }}</span></p><p class="stat-label">ผู้เข้าร่วมแล้ว (คน)</p></div>
        <div class="stat bg-white shadow-soft"><p class="stat-value {{ $isFull ? 'text-bad' : '' }}">{{ max(0, $activity->capacity - $approvedTotal) }}</p><p class="stat-label">{{ $isFull ? 'เต็มแล้ว' : 'ที่ว่างคงเหลือ' }}</p></div>
    </div>

    {{-- กิจกรรมเริ่มแล้ว: เตือนให้เช็กชื่อ เพราะผู้ที่ถูกเช็กว่า "มา" เท่านั้นจึงรีวิวได้ (ส่วนที่ 5) --}}
    @if($activity->status !== 'cancelled' && $activity->starts_at->isPast())
        @if($statusFilter !== 'approved')
            <div class="mt-6 flex flex-col gap-3 rounded-tile bg-brand-50 p-4 text-sm text-brand-700 sm:flex-row sm:items-center sm:justify-between">
                <p class="flex items-start gap-2"><x-ui.icon name="check-circle" class="mt-0.5 h-4 w-4" /> กิจกรรมเริ่มแล้ว เช็กชื่อ “มา/ขาด” ได้ในแท็บอนุมัติแล้ว ผู้ที่ถูกเช็กว่ามาเท่านั้นจึงรีวิวกิจกรรมได้</p>
                <a href="{{ route('activities.requests', ['activity' => $activity, 'status' => 'approved']) }}" class="btn btn-primary btn-sm shrink-0">ไปเช็กชื่อ</a>
            </div>
        @elseif(($unchecked = $participants->whereNull('attendance')->count()) > 0)
            <p class="mt-6 flex items-center gap-2 rounded-tile bg-warn-soft p-4 text-sm text-warn"><x-ui.icon name="clock" class="h-4 w-4" /> ยังไม่ได้เช็กชื่อ {{ $unchecked }} คน</p>
        @else
            <p class="mt-6 flex items-center gap-2 rounded-tile bg-ok-soft p-4 text-sm text-ok"><x-ui.icon name="check" class="h-4 w-4" /> เช็กชื่อครบทุกคนแล้ว</p>
        @endif
    @endif

    <div class="card mt-6 p-2 sm:p-3">
        {{-- แท็บกรองสถานะ --}}
        <nav class="tabs mx-3" aria-label="กรองตามสถานะคำขอ">
            @foreach($tabs as $key => $label)
                @php $count = $key === 'all' ? $counts->sum() : ($counts[$key] ?? 0); @endphp
                <a href="{{ route('activities.requests', ['activity' => $activity, 'status' => $key]) }}" @if($statusFilter === $key) aria-current="page" @endif
                   class="tab {{ $statusFilter === $key ? 'tab-active' : '' }}">{{ $label }} <span class="tab-count">{{ $count }}</span></a>
            @endforeach
        </nav>

        <ul class="divide-y divide-line">
            @forelse($participants as $p)
                <li class="flex flex-col gap-4 px-3 py-5 sm:flex-row sm:items-start">
                    <div class="flex min-w-0 flex-1 items-start gap-3.5">
                        <x-ui.avatar :user="$p->user" size="lg" />
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="break-anywhere font-medium text-ink">{{ $p->user->name }}</p>
                                @if($p->user->student_id)
                                    <span class="text-xs text-ink-muted">({{ $p->user->student_id }})</span>
                                @endif
                                @if($p->isPending())
                                    <span class="chip chip-warn h-6 px-2.5 text-xs">รออนุมัติ</span>
                                @elseif($p->isApproved())
                                    <span class="chip chip-ok h-6 px-2.5 text-xs">อนุมัติแล้ว</span>
                                @elseif($p->isRejected())
                                    <span class="chip chip-bad h-6 px-2.5 text-xs">ปฏิเสธแล้ว</span>
                                @else
                                    <span class="chip h-6 px-2.5 text-xs">ถอนตัวแล้ว</span>
                                @endif
                            </div>
                            @if($p->message)
                                <p class="break-anywhere mt-2 inline-block max-w-full rounded-2xl rounded-tl-md bg-canvas px-3.5 py-2 text-sm text-ink-soft"><span class="sr-only">ข้อความ: </span>{{ $p->message }}</p>
                            @endif
                            <p class="mt-1.5 text-xs text-ink-faint">ส่งคำขอเมื่อ <x-ui.time :value="$p->created_at" mode="relative" /></p>
                        </div>
                    </div>

                    {{-- ปุ่มจัดการ (แบ่งตามสถานะ) --}}
                    <div class="flex shrink-0 flex-wrap items-center gap-2 sm:justify-end sm:pt-1">
                        {{-- กรณีสถานะเป็น pending: แสดงปุ่ม อนุมัติ / ปฏิเสธ --}}
                        @if($p->isPending())
                            @if(! $isFull)
                                <form method="POST" action="{{ route('activities.requests.approve', [$activity, $p]) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-success btn-sm"><x-ui.icon name="check" class="h-4 w-4" /> อนุมัติ</button>
                                </form>
                            @else
                                <span class="chip chip-bad">เต็มแล้ว</span>
                            @endif
                            <form method="POST" action="{{ route('activities.requests.reject', [$activity, $p]) }}" onsubmit="return confirm('ยืนยันปฏิเสธคำขอของ {{ $p->user->name }}?')">
                                @csrf @method('PATCH')
                                <button class="btn btn-danger btn-sm">ปฏิเสธ</button>
                            </form>
                        @endif

                        {{-- กรณีสถานะเป็น approved: แสดงปุ่มเช็กชื่อ มา / ขาด แบบ segmented --}}
                        @if($p->isApproved())
                            <form action="{{ route('activities.requests.attendance', [$activity->id, $p->id]) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <span class="text-xs text-ink-muted">เช็กชื่อ</span>
                                <span class="inline-flex rounded-full bg-canvas p-1">
                                    <button type="submit" name="attendance" value="present" aria-pressed="{{ $p->attendance === 'present' ? 'true' : 'false' }}"
                                            class="h-8 rounded-full px-4 text-sm font-medium transition {{ $p->attendance === 'present' ? 'bg-ok text-white shadow-soft' : 'text-ink-muted hover:text-ok' }}">มา</button>
                                    <button type="submit" name="attendance" value="absent" aria-pressed="{{ $p->attendance === 'absent' ? 'true' : 'false' }}"
                                            class="h-8 rounded-full px-4 text-sm font-medium transition {{ $p->attendance === 'absent' ? 'bg-bad text-white shadow-soft' : 'text-ink-muted hover:text-bad' }}">ขาด</button>
                                </span>
                            </form>
                        @endif
                    </div>
                </li>
            @empty
                <li class="flex flex-col items-center px-6 py-14 text-center">
                    <x-ui.empty-art name="empty-requests" />
                    <p class="mt-4 text-ink-muted">ไม่มีคำขอในสถานะนี้</p>
                </li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
