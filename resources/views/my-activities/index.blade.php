@extends('layouts.app')
@section('title', 'นัดของฉัน | UniMate')

@php
    $tintFor = fn ($activity) => ($activity->status === 'cancelled' || $activity->isEnded()) ? 'bg-canvas' : App\Support\Ui::tint($activity->category_id);
    $stats = [
        ['กิจกรรมที่ฉันสร้าง', $myActivities->count()],
        ['คำขอใหม่รอฉันอนุมัติ', $myActivities->sum('pending_count')],
        ['คำขอของฉันที่รออนุมัติ', $myParticipations->where('status', 'pending')->count()],
        ['กิจกรรมที่ได้เข้าร่วม', $myParticipations->where('status', 'approved')->count()],
    ];
@endphp

@section('content')
<div class="mx-auto max-w-6xl">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">นัดของฉัน</h1>
            <p class="mt-2 text-ink-muted">กิจกรรมที่คุณสร้างและกิจกรรมที่คุณขอเข้าร่วม</p>
        </div>
        <a href="{{ route('activities.create') }}" class="btn btn-primary hidden sm:inline-flex"><x-ui.icon name="plus" class="h-4 w-4" /> สร้างโพสต์</a>
    </div>

    {{-- ผู้ใช้ใหม่ที่ยังไม่มีอะไรเลย ไม่ต้องแสดงตัวเลขศูนย์ทั้งแถว (กล่องว่างด้านล่างบอกทางไปต่อแล้ว) --}}
    @if(collect($stats)->sum(1) > 0)
        <dl class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach($stats as [$label, $value])
                <div class="stat flex flex-col-reverse bg-white shadow-soft"><dt class="stat-label">{{ $label }}</dt><dd class="stat-value">{{ $value }}</dd></div>
            @endforeach
        </dl>
    @endif

    {{-- ======================== กิจกรรมที่ฉันสร้าง ======================== --}}
    <section class="mt-10" aria-labelledby="mine-heading">
        <h2 id="mine-heading" class="mb-4 flex items-center gap-2 text-xl font-medium text-ink">กิจกรรมที่ฉันสร้าง <span class="tab-count">{{ $myActivities->count() }}</span></h2>
        @if($myActivities->isNotEmpty())
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($myActivities as $activity)
                    <article class="card reveal flex flex-col p-5" style="--i: {{ $loop->index }}">
                        <div class="flex items-start gap-4 pb-5">
                            <x-ui.date-tile :date="$activity->starts_at" :tint="$tintFor($activity)" />
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="chip chip-outline">{{ $activity->category->name }}</span>
                                    <x-ui.activity-status :state="App\Support\Ui::activityState($activity, $activity->approved_count)" />
                                </div>
                                <h3 class="break-anywhere mt-2 text-[1.0625rem] font-medium leading-snug text-ink"><a href="{{ route('activities.show', $activity) }}" class="hover:underline">{{ $activity->title }}</a></h3>
                                <p class="mt-1 text-sm text-ink-muted">วันเริ่ม: {{ $activity->starts_at->copy()->locale('th')->isoFormat('dd D MMM') }} · {{ $activity->starts_at->format('H:i') }}</p>
                            </div>
                        </div>
                        <div class="mt-auto flex flex-wrap items-center gap-3 border-t border-line pt-4 text-sm">
                            @php $started = $activity->status !== 'cancelled' && $activity->starts_at->isPast(); @endphp
                            <span class="flex items-center gap-1.5 tabular-nums text-ink-soft"><x-ui.icon name="users" class="h-4 w-4 text-ink-faint" /><span class="font-medium text-ok">{{ $activity->approved_count }} เข้าร่วม</span> / {{ $activity->capacity }} ที่</span>
                            {{-- กิจกรรมเริ่มแล้ว: พาไปเช็กชื่อ (แท็บอนุมัติแล้ว) แทนรายการคำขอ --}}
                            <a href="{{ $started ? route('activities.requests', ['activity' => $activity, 'status' => 'approved']) : route('activities.requests', $activity) }}" class="ml-auto inline-flex items-center gap-1.5 {{ ! $started && $activity->pending_count > 0 ? 'chip chip-warn hover:brightness-95' : 'text-ink-muted hover:text-ink' }}">
                                @if($started)
                                    เช็กชื่อผู้เข้าร่วม
                                @elseif($activity->pending_count > 0)
                                    {{ $activity->pending_count }} คำขอรอ
                                @else
                                    จัดการคำขอ
                                @endif
                                <x-ui.icon name="chevron-right" class="h-4 w-4" />
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center rounded-card border border-dashed border-line-strong bg-white/60 px-6 py-10 text-center text-ink-muted">
                <x-ui.icon name="calendar" class="h-7 w-7" />
                <p class="mt-3">คุณยังไม่ได้สร้างกิจกรรม</p>
                <a href="{{ route('activities.create') }}" class="btn btn-primary btn-sm mt-4"><x-ui.icon name="plus" class="h-4 w-4" /> สร้างกิจกรรมใหม่</a>
            </div>
        @endif
    </section>

    {{-- ======================== กิจกรรมที่ฉันขอเข้าร่วม ======================== --}}
    <section class="mt-12" aria-labelledby="joined-heading">
        <h2 id="joined-heading" class="mb-4 flex items-center gap-2 text-xl font-medium text-ink">กิจกรรมที่ฉันขอเข้าร่วม <span class="tab-count">{{ $myParticipations->count() }}</span></h2>
        @if($myParticipations->isNotEmpty())
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($myParticipations as $participation)
                    @php $act = $participation->activity; @endphp
                    <article class="card reveal flex flex-col p-5" style="--i: {{ $loop->index }}">
                        <div class="flex items-start gap-4">
                            <x-ui.date-tile :date="$act->starts_at" :tint="$tintFor($act)" />
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="chip chip-outline">{{ $act->category->name }}</span>
                                    @if($participation->isPending())
                                        <span class="chip chip-warn">รออนุมัติ</span>
                                    @elseif($participation->isApproved())
                                        <span class="chip chip-ok">เข้าร่วมแล้ว</span>

                                        {{-- แสดงสถานะการเช็กชื่อของผู้จัด --}}
                                        @if($participation->attendance === 'present')
                                            <span class="chip chip-ok"><x-ui.icon name="check" class="h-3.5 w-3.5" /> มาแล้ว</span>
                                        @elseif($participation->attendance === 'absent')
                                            <span class="chip chip-bad">ขาด</span>
                                        @else
                                            <span class="chip">รอเช็กชื่อ</span>
                                        @endif

                                    @elseif($participation->isRejected())
                                        <span class="chip chip-bad">ถูกปฏิเสธ</span>
                                    @else
                                        <span class="chip">ถอนตัวแล้ว</span>
                                    @endif
                                </div>
                                <h3 class="break-anywhere mt-2 text-[1.0625rem] font-medium leading-snug text-ink"><a href="{{ route('activities.show', $act) }}" class="hover:underline">{{ $act->title }}</a></h3>
                                <p class="mt-1 flex items-center gap-1.5 text-sm text-ink-muted"><x-ui.avatar :user="$act->user" size="xs" /> โดย {{ $act->user->name }} · {{ $act->starts_at->copy()->locale('th')->isoFormat('dd D MMM') }} {{ $act->starts_at->format('H:i') }}</p>
                            </div>
                        </div>

                        {{-- ถอนตัวได้ก่อนกิจกรรมเริ่ม (เงื่อนไขเดียวกับหน้ารายละเอียดกิจกรรม) --}}
                        @if(in_array($participation->status, ['pending', 'approved']) && $act->status === 'published' && $act->starts_at->isFuture())
                            <div class="mt-5 flex justify-end border-t border-line pt-4">
                                <form method="POST" action="{{ route('activities.cancel-request', $act) }}" onsubmit="return confirm('{{ $participation->isApproved() ? 'ยืนยันถอนตัวจากกิจกรรม?' : 'ยืนยันถอนคำขอ?' }}')">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-danger btn-sm">
                                        {{ $participation->isApproved() ? 'ถอนตัว' : 'ถอนคำขอ' }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center rounded-card border border-dashed border-line-strong bg-white/60 px-6 py-10 text-center text-ink-muted">
                <x-ui.icon name="search" class="h-7 w-7" />
                <p class="mt-3">คุณยังไม่ได้ขอเข้าร่วมกิจกรรมใด</p>
                <a href="{{ route('activities.index') }}" class="btn btn-secondary btn-sm mt-4">ค้นหากิจกรรม</a>
            </div>
        @endif
    </section>
</div>
@endsection
