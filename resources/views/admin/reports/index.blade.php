@extends('layouts.app')
@section('title', 'ตรวจรายงาน (Admin) | UniMate')
@section('content')
<div class="space-y-6">
    @include('admin.partials.header', [
        'title' => 'ตรวจรายงานและจัดการเนื้อหา',
        'description' => 'ตรวจรายงานจากผู้ใช้ ซ่อนกิจกรรมหรือระงับผู้ใช้ที่กระทำผิด และบันทึกเหตุผลทุกครั้ง',
    ])
    @include('activities.errors')

    @php
        $tabs = ['pending' => 'รอตรวจ', 'actioned' => 'ดำเนินการแล้ว', 'dismissed' => 'ยกรายงาน', 'all' => 'ทั้งหมด'];
        $statusStyles = ['pending' => 'chip-warn', 'actioned' => 'chip-bad', 'dismissed' => ''];
    @endphp
    <nav class="flex flex-wrap gap-2" aria-label="กรองตามสถานะรายงาน">
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.reports.index', ['status' => $key]) }}" @if($status === $key) aria-current="page" @endif
               class="chip h-9 px-4 text-sm transition {{ $status === $key ? 'chip-dark' : 'chip-outline hover:border-ink-faint' }}">
                {{ $label }}
                @if($key !== 'all')<span class="opacity-70">({{ $counts[$key] ?? 0 }})</span>@endif
            </a>
        @endforeach
    </nav>

    <section class="space-y-4">
        @forelse($reports as $report)
            @php
                $target = $report->target();
                $isReview = $report->target_type === 'review';
                $iconTint = ['activity' => 'bg-tint-butter', 'review' => 'bg-tint-mint'][$report->target_type] ?? 'bg-tint-lilac';
                $iconName = ['activity' => 'calendar', 'review' => 'star'][$report->target_type] ?? 'user';
            @endphp
            <article class="card p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full {{ $iconTint }}"><x-ui.icon :name="$iconName" /></span>
                        <div class="min-w-0">
                            <p class="text-xs text-ink-muted">#{{ $report->id }} · <x-ui.time :value="$report->created_at" /> · รายงานโดย <a href="{{ route('users.show', $report->reporter) }}" class="hover:underline underline-offset-4">{{ $report->reporter->name }}</a></p>
                            <p class="mt-1 break-words font-medium text-ink">
                                {{ $report->targetLabel() }}:
                                @if($target === null)
                                    <span class="text-ink-faint">{{ $isReview ? 'รีวิวถูกลบแล้ว' : '(ถูกลบแล้ว)' }}</span>
                                @elseif($target instanceof \App\Models\Review)
                                    ของ <x-ui.user-link :user="$target->user" class="link">{{ $target->user->name }}</x-ui.user-link>
                                    <span class="text-sm font-normal text-ink-muted">ในกิจกรรม</span> <a href="{{ route('activities.show', $target->activity) }}#reviews" class="link">{{ $target->activity->title }}</a>
                                @elseif($target instanceof \App\Models\Activity)
                                    <a href="{{ route('activities.show', $target) }}" class="link">{{ $target->title }}</a>
                                    @if($target->isHidden())<span class="chip chip-dark ml-1 h-6 px-2.5 text-xs">ซ่อนอยู่</span>@endif
                                @else
                                    <a href="{{ route('users.show', $target) }}" class="link">{{ $target->name }}</a> <span class="text-sm font-normal text-ink-muted">({{ $target->email }})</span>
                                    @if($target->isSuspended())<span class="chip chip-bad ml-1 h-6 px-2.5 text-xs">ถูกระงับ</span>@endif
                                @endif
                            </p>
                        </div>
                    </div>
                    <span class="chip {{ $statusStyles[$report->status] ?? '' }}">{{ $tabs[$report->status] ?? $report->status }}</span>
                </div>
                @if($target instanceof \App\Models\Review)
                    {{-- เนื้อหารีวิวที่ถูกรายงาน ให้ Admin ตัดสินใจได้โดยไม่ต้องเปิดหน้ากิจกรรม --}}
                    <div class="mt-4 rounded-tile border border-line p-4">
                        <p class="flex" role="img" aria-label="{{ $target->rating }} จาก 5 ดาว">@for($i = 1; $i <= 5; $i++)<x-ui.icon name="star" solid class="h-4 w-4 {{ $i <= $target->rating ? 'text-sun' : 'text-line-strong' }}" />@endfor</p>
                        <p class="break-anywhere mt-2 whitespace-pre-wrap text-sm leading-relaxed text-ink-soft">{{ $target->comment ?: '(ไม่มีความคิดเห็น ให้คะแนนอย่างเดียว)' }}</p>
                    </div>
                @endif
                <p class="break-anywhere mt-4 whitespace-pre-wrap rounded-tile bg-canvas p-4 text-sm leading-relaxed text-ink-soft"><span class="text-ink-muted">เหตุผลที่รายงาน:</span> {{ $report->reason }}</p>

                @if($report->isPending())
                    <form method="POST" action="{{ route('admin.reports.resolve', $report) }}" class="mt-4 space-y-3">
                        @csrf @method('PATCH')
                        <label for="note-{{ $report->id }}" class="sr-only">บันทึกเหตุผลการดำเนินการ</label>
                        <input id="note-{{ $report->id }}" name="note" required minlength="3" maxlength="1000" placeholder="บันทึกเหตุผลการดำเนินการ (บังคับ)" class="field">
                        <div class="flex flex-wrap gap-2">
                            {{-- เนื้อหาที่ถูกลบไปแล้ว (เช่น Admin ลบรีวิวจากหน้ากิจกรรม) เหลือแค่ปุ่มยกรายงาน --}}
                            @if($target !== null)
                                @php
                                    $hideLabel = ['activity' => 'ซ่อนกิจกรรม', 'review' => 'ลบรีวิว'][$report->target_type] ?? 'ระงับบัญชีผู้ใช้';
                                    $hideIcon = ['activity' => 'eye-slash', 'review' => 'trash'][$report->target_type] ?? 'ban';
                                @endphp
                                <button name="action" value="hide" class="btn btn-sm bg-bad text-white hover:brightness-110" onclick="return confirm('ยืนยัน{{ $hideLabel }}?')">
                                    <x-ui.icon :name="$hideIcon" class="h-4 w-4" />
                                    {{ $hideLabel }}
                                </button>
                            @endif
                            <button name="action" value="dismiss" class="btn btn-secondary btn-sm">ยกรายงาน (ไม่พบความผิด)</button>
                        </div>
                    </form>
                @else
                    <p class="break-anywhere mt-4 text-sm text-ink-muted">ดำเนินการโดย <span class="text-ink-soft">{{ $report->handler?->name ?? '-' }}</span>@if($report->handled_at) เมื่อ <x-ui.time :value="$report->handled_at" />@endif · เหตุผล: {{ $report->admin_note }}</p>
                @endif
            </article>
        @empty
            <div class="card flex flex-col items-center px-6 py-12 text-center">
                <span class="grid h-14 w-14 place-items-center rounded-2xl bg-ok-soft text-ok"><x-ui.icon name="check-circle" class="h-6 w-6" /></span>
                <p class="mt-4 text-ink-muted">ไม่มีรายงานในหมวดนี้</p>
            </div>
        @endforelse
    </section>

    @if($hiddenActivities->isNotEmpty())
        <section>
            <h2 class="mb-3 text-xl font-medium text-ink">กิจกรรมที่ถูกซ่อน ({{ $hiddenActivities->count() }})</h2>
            <div class="space-y-3">
                @foreach($hiddenActivities as $hidden)
                    <div class="card flex flex-wrap items-end justify-between gap-3 p-5">
                        <div class="min-w-0">
                            <a href="{{ route('activities.show', $hidden) }}" class="link">{{ $hidden->title }}</a>
                            <p class="break-anywhere mt-1 text-xs text-ink-muted">ซ่อนเมื่อ <x-ui.time :value="$hidden->hidden_at" /> · {{ $hidden->hidden_reason }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.activities.unhide', $hidden) }}" class="flex w-full gap-2 sm:w-auto">
                            @csrf @method('PATCH')
                            <label for="unhide-{{ $hidden->id }}" class="sr-only">เหตุผลการเลิกซ่อน</label>
                            <input id="unhide-{{ $hidden->id }}" name="note" required minlength="3" maxlength="1000" placeholder="เหตุผลการเลิกซ่อน" class="field h-10 min-w-0 flex-1 rounded-full text-sm sm:w-56">
                            <button class="btn btn-success btn-sm h-10">เลิกซ่อน</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section>
        <h2 class="mb-3 text-xl font-medium text-ink">ประวัติการดำเนินการ</h2>
        <div class="card overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm">
                <thead class="bg-canvas/70 text-left text-xs text-ink-muted">
                    <tr><th class="px-4 py-3 font-medium">เวลา</th><th class="px-4 py-3 font-medium">ผู้ดูแล</th><th class="px-4 py-3 font-medium">การดำเนินการ</th><th class="px-4 py-3 font-medium">เป้าหมาย</th><th class="px-4 py-3 font-medium">เหตุผล</th></tr>
                </thead>
                <tbody class="divide-y divide-line text-ink-soft">
                    @forelse($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 tabular-nums text-ink-muted"><x-ui.time :value="$log->created_at" /></td>
                            <td class="px-4 py-3">{{ $log->admin->name }}</td>
                            <td class="px-4 py-3"><span class="chip h-6 px-2.5 text-xs">{{ $log->actionLabel() }}</span></td>
                            <td class="px-4 py-3">{{ $log->targetLabel() }}</td>
                            <td class="break-words px-4 py-3">{{ $log->reason }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-ink-faint">ยังไม่มีประวัติการดำเนินการ</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
