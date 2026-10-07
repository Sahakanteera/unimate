@extends('layouts.app')
@section('title', 'ตรวจรายงาน (Admin) | UniMate')
@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-3xl font-bold mb-3">ตรวจรายงานและจัดการเนื้อหา</h1>
    <p class="text-slate-500 mb-6">ตรวจรายงานจากผู้ใช้ ซ่อนกิจกรรมหรือระงับผู้ใช้ที่กระทำผิด และบันทึกเหตุผลทุกครั้ง</p>
    @include('activities.errors')

    @php
        $tabs = ['pending' => 'รอตรวจ', 'actioned' => 'ดำเนินการแล้ว', 'dismissed' => 'ยกรายงาน', 'all' => 'ทั้งหมด'];
        $statusStyles = ['pending' => 'bg-amber-50 text-amber-700', 'actioned' => 'bg-rose-50 text-rose-700', 'dismissed' => 'bg-slate-100 text-slate-600'];
    @endphp
    <div class="flex flex-wrap gap-2 mb-5">
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.reports.index', ['status' => $key]) }}" class="px-4 py-2 rounded-xl text-sm border {{ $status === $key ? 'bg-slate-900 text-white border-slate-900' : 'bg-white hover:bg-slate-50' }}">
                {{ $label }}
                @if($key !== 'all')<span class="ml-1 opacity-70">({{ $counts[$key] ?? 0 }})</span>@endif
            </a>
        @endforeach
    </div>

    <section class="space-y-4 mb-10">
        @forelse($reports as $report)
            @php $target = $report->target(); @endphp
            <div class="bg-white border rounded-2xl p-5">
                <div class="flex flex-wrap justify-between gap-3">
                    <div>
                        <p class="text-xs text-slate-500">#{{ $report->id }} · {{ $report->created_at->format('d/m/Y H:i') }} · รายงานโดย {{ $report->reporter->name }}</p>
                        <p class="font-semibold mt-1">
                            {{ $report->target_type === 'activity' ? 'กิจกรรม' : 'ผู้ใช้' }}:
                            @if($target === null)
                                <span class="text-slate-400">(ถูกลบแล้ว)</span>
                            @elseif($target instanceof \App\Models\Activity)
                                <a href="{{ route('activities.show', $target) }}" class="text-blue-600 hover:underline">{{ $target->title }}</a>
                                @if($target->isHidden())<span class="text-xs bg-slate-800 text-white rounded-full px-2 py-0.5 ml-1">ซ่อนอยู่</span>@endif
                            @else
                                {{ $target->name }} <span class="text-sm text-slate-500">({{ $target->email }})</span>
                                @if($target->isSuspended())<span class="text-xs bg-rose-600 text-white rounded-full px-2 py-0.5 ml-1">ถูกระงับ</span>@endif
                            @endif
                        </p>
                    </div>
                    <span class="self-start text-xs rounded-full px-3 py-1 {{ $statusStyles[$report->status] ?? '' }}">{{ $tabs[$report->status] ?? $report->status }}</span>
                </div>
                <p class="mt-3 text-sm bg-slate-50 rounded-xl p-3 whitespace-pre-wrap break-words"><span class="text-slate-500">เหตุผลที่รายงาน:</span> {{ $report->reason }}</p>

                @if($report->isPending())
                    <form method="POST" action="{{ route('admin.reports.resolve', $report) }}" class="mt-4 space-y-3">
                        @csrf @method('PATCH')
                        <input name="note" required minlength="3" maxlength="1000" placeholder="บันทึกเหตุผลการดำเนินการ (บังคับ)" class="block w-full border rounded-xl p-2.5 text-sm">
                        <div class="flex flex-wrap gap-2">
                            <button name="action" value="hide" class="bg-rose-600 text-white rounded-xl px-4 py-2 text-sm hover:bg-rose-700" onclick="return confirm('ยืนยันดำเนินการ?')">
                                {{ $report->target_type === 'activity' ? 'ซ่อนกิจกรรม' : 'ระงับบัญชีผู้ใช้' }}
                            </button>
                            <button name="action" value="dismiss" class="bg-slate-100 rounded-xl px-4 py-2 text-sm hover:bg-slate-200">ยกรายงาน (ไม่พบความผิด)</button>
                        </div>
                    </form>
                @else
                    <p class="mt-3 text-sm text-slate-600">ดำเนินการโดย {{ $report->handler?->name ?? '-' }} เมื่อ {{ $report->handled_at?->format('d/m/Y H:i') }} · เหตุผล: {{ $report->admin_note }}</p>
                @endif
            </div>
        @empty
            <p class="text-slate-500 bg-white border rounded-2xl p-8 text-center">ไม่มีรายงานในหมวดนี้</p>
        @endforelse
    </section>

    @if($hiddenActivities->isNotEmpty())
    <section class="mb-10">
        <h2 class="text-xl font-semibold mb-3">กิจกรรมที่ถูกซ่อน ({{ $hiddenActivities->count() }})</h2>
        <div class="space-y-3">
            @foreach($hiddenActivities as $hidden)
                <div class="bg-white border rounded-2xl p-4 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <a href="{{ route('activities.show', $hidden) }}" class="font-medium text-blue-600 hover:underline">{{ $hidden->title }}</a>
                        <p class="text-xs text-slate-500">ซ่อนเมื่อ {{ $hidden->hidden_at->format('d/m/Y H:i') }} · {{ $hidden->hidden_reason }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.activities.unhide', $hidden) }}" class="flex gap-2">
                        @csrf @method('PATCH')
                        <input name="note" required minlength="3" maxlength="1000" placeholder="เหตุผลการเลิกซ่อน" class="border rounded-xl p-2 text-sm">
                        <button class="bg-emerald-600 text-white rounded-xl px-4 py-2 text-sm hover:bg-emerald-700">เลิกซ่อน</button>
                    </form>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <section>
        <h2 class="text-xl font-semibold mb-3">ประวัติการดำเนินการ</h2>
        <div class="bg-white border rounded-2xl overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr><th class="p-3">เวลา</th><th class="p-3">ผู้ดูแล</th><th class="p-3">การดำเนินการ</th><th class="p-3">เป้าหมาย</th><th class="p-3">เหตุผล</th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($logs as $log)
                        <tr>
                            <td class="p-3 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-3">{{ $log->admin->name }}</td>
                            <td class="p-3">{{ $log->actionLabel() }}</td>
                            <td class="p-3">{{ $log->targetLabel() }}</td>
                            <td class="p-3 break-words">{{ $log->reason }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-slate-400">ยังไม่มีประวัติการดำเนินการ</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
