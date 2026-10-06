@extends('layouts.app')
@section('title', 'จัดการคำขอ — '.$activity->title.' | UniMate')
@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('activities.show', $activity) }}" class="text-blue-600">← กลับหน้ากิจกรรม</a>

    <div class="mt-5 bg-white border rounded-2xl p-6 sm:p-8">
        <h1 class="text-2xl font-bold mb-1">จัดการคำขอเข้าร่วม</h1>
        <p class="text-slate-500 mb-6">{{ $activity->title }} · ผู้เข้าร่วม {{ $activity->approvedCount() }}/{{ $activity->capacity }} คน</p>

        {{-- แท็บกรองสถานะ --}}
        <div class="flex flex-wrap gap-2 mb-6 border-b pb-4">
            @php
                $tabs = [
                    'pending'  => ['label' => 'รออนุมัติ',  'color' => 'amber'],
                    'approved' => ['label' => 'อนุมัติแล้ว', 'color' => 'emerald'],
                    'rejected' => ['label' => 'ปฏิเสธแล้ว', 'color' => 'rose'],
                    'all'      => ['label' => 'ทั้งหมด',    'color' => 'slate'],
                ];
            @endphp
            @foreach($tabs as $key => $tab)
                @php $count = $key === 'all' ? $counts->sum() : ($counts[$key] ?? 0); @endphp
                <a href="{{ route('activities.requests', ['activity' => $activity, 'status' => $key]) }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium transition-colors {{ $statusFilter === $key ? 'bg-'.$tab['color'].'-600 text-white' : 'bg-'.$tab['color'].'-50 text-'.$tab['color'].'-700 hover:bg-'.$tab['color'].'-100' }}">
                    {{ $tab['label'] }} ({{ $count }})
                </a>
            @endforeach
        </div>

        @forelse($participants as $p)
        <div class="flex items-start gap-4 p-4 rounded-xl {{ $loop->even ? 'bg-slate-50' : '' }} mb-2">
            {{-- Avatar --}}
            <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-sm overflow-hidden flex-shrink-0">
                @if($p->user->avatar && Storage::disk('public')->exists($p->user->avatar))
                    <img src="{{ asset('storage/' . $p->user->avatar) }}" alt="{{ $p->user->name }}" class="w-full h-full object-cover">
                @else
                    {{ $p->user->initials() }}
                @endif
            </div>

            {{-- ข้อมูลผู้ขอ --}}
            <div class="flex-grow min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="font-semibold">{{ $p->user->name }}</p>
                    @if($p->user->student_id)
                        <span class="text-xs text-slate-500">({{ $p->user->student_id }})</span>
                    @endif
                    @if($p->isPending())
                        <span class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700">รออนุมัติ</span>
                    @elseif($p->isApproved())
                        <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700">อนุมัติแล้ว</span>
                    @elseif($p->isRejected())
                        <span class="px-2 py-0.5 rounded-full text-xs bg-rose-100 text-rose-700">ปฏิเสธแล้ว</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">ถอนตัวแล้ว</span>
                    @endif
                </div>
                @if($p->message)
                    <p class="text-sm text-slate-600 mt-1 break-words">ข้อความ: {{ $p->message }}</p>
                @endif
                <p class="text-xs text-slate-400 mt-1">ส่งคำขอเมื่อ {{ $p->created_at->format('d/m/Y H:i') }}</p>
            </div>

            {{-- ปุ่มจัดการ (แบ่งตามสถานะ) --}}
            <div class="flex gap-2 flex-shrink-0 items-center">
                {{-- กรณีสถานะเป็น pending: แสดงปุ่ม ออนุมัติ / ปฏิเสธ --}}
                @if($p->isPending())
                    @if(! $activity->isFull())
                    <form method="POST" action="{{ route('activities.requests.approve', [$activity, $p]) }}">
                        @csrf @method('PATCH')
                        <button class="bg-emerald-600 text-white text-sm rounded-xl px-4 py-2 hover:bg-emerald-700 transition-colors">อนุมัติ</button>
                    </form>
                    @else
                    <span class="text-xs text-rose-500 self-center">เต็มแล้ว</span>
                    @endif
                    <form method="POST" action="{{ route('activities.requests.reject', [$activity, $p]) }}" onsubmit="return confirm('ยืนยันปฏิเสธคำขอของ {{ $p->user->name }}?')">
                        @csrf @method('PATCH')
                        <button class="bg-rose-50 text-rose-700 border border-rose-200 text-sm rounded-xl px-4 py-2 hover:bg-rose-100 transition-colors">ปฏิเสธ</button>
                    </form>
                @endif

                {{-- กรณีสถานะเป็น approved: แสดงปุ่มเช็กชื่อ มา / ขาด --}}
                @if($p->isApproved())
                    <form action="{{ route('activities.requests.attendance', [$activity->id, $p->id]) }}" method="POST" class="flex gap-2">
                        @csrf
                        @method('PATCH')
                        <button type="submit" name="attendance" value="present" 
                                class="text-xs font-medium px-3 py-2 rounded-xl border transition-colors {{ $p->attendance === 'present' ? 'bg-emerald-600 text-white border-emerald-600' : 'text-emerald-700 border-emerald-300 bg-emerald-50 hover:bg-emerald-100' }}">
                            มา
                        </button>
                        <button type="submit" name="attendance" value="absent" 
                                class="text-xs font-medium px-3 py-2 rounded-xl border transition-colors {{ $p->attendance === 'absent' ? 'bg-rose-600 text-white border-rose-600' : 'text-rose-700 border-rose-300 bg-rose-50 hover:bg-rose-100' }}">
                            ขาด
                        </button>
                    </form>
                @endif
            </div>
        </div>
        @empty
        <div class="border border-dashed rounded-2xl p-12 text-center text-slate-500">
            ไม่มีคำขอในสถานะนี้
        </div>
        @endforelse
    </div>
</div>
@endsection