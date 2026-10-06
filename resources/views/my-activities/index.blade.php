@extends('layouts.app')
@section('title', 'นัดของฉัน | UniMate')
@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-3xl font-bold mb-2">นัดของฉัน</h1>
    <p class="text-slate-500 mb-8">กิจกรรมที่คุณสร้างและกิจกรรมที่คุณขอเข้าร่วม</p>

    {{-- ======================== กิจกรรมที่ฉันสร้าง ======================== --}}
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">กิจกรรมที่ฉันสร้าง <span class="text-sm font-normal text-slate-500">({{ $myActivities->count() }})</span></h2>
        @if($myActivities->isNotEmpty())
        <div class="grid md:grid-cols-2 gap-5">
            @foreach($myActivities as $activity)
            <article class="bg-white border rounded-2xl p-5 shadow-sm">
                <div class="flex flex-wrap gap-2 text-sm mb-2">
                    <span class="bg-blue-50 text-blue-700 rounded-full px-3 py-1">{{ $activity->category->name }}</span>
                    <span class="rounded-full px-3 py-1 {{ $activity->status === 'cancelled' ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $activity->status === 'cancelled' ? 'ยกเลิกแล้ว' : 'ประกาศแล้ว' }}</span>
                </div>
                <h3 class="text-lg font-semibold break-words"><a href="{{ route('activities.show', $activity) }}" class="hover:text-blue-600">{{ $activity->title }}</a></h3>
                <p class="text-sm text-slate-500 mt-2">วันเริ่ม: {{ $activity->starts_at->format('d/m/Y H:i') }}</p>

                <div class="flex items-center gap-4 mt-4 pt-3 border-t text-sm">
                    <span class="text-emerald-600 font-medium">{{ $activity->approved_count }} เข้าร่วม</span>
                    <span class="text-slate-400">/</span>
                    <span>{{ $activity->capacity }} ที่</span>
                    @if($activity->pending_count > 0)
                        <a href="{{ route('activities.requests', $activity) }}" class="ml-auto bg-amber-50 text-amber-700 border border-amber-200 rounded-full px-3 py-1 hover:bg-amber-100 transition-colors">
                            {{ $activity->pending_count }} คำขอรอ
                        </a>
                    @endif
                </div>
            </article>
            @endforeach
        </div>
        @else
        <div class="border border-dashed rounded-2xl p-10 text-center text-slate-500">คุณยังไม่ได้สร้างกิจกรรม · <a href="{{ route('activities.create') }}" class="text-blue-600">สร้างกิจกรรมใหม่</a></div>
        @endif
    </section>

    {{-- ======================== กิจกรรมที่ฉันขอเข้าร่วม ======================== --}}
    <section>
        <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">กิจกรรมที่ฉันขอเข้าร่วม <span class="text-sm font-normal text-slate-500">({{ $myParticipations->count() }})</span></h2>
        @if($myParticipations->isNotEmpty())
        <div class="grid md:grid-cols-2 gap-5">
            @foreach($myParticipations as $participation)
            @php $act = $participation->activity; @endphp
            <article class="bg-white border rounded-2xl p-5 shadow-sm">
                <div class="flex flex-wrap gap-2 text-sm mb-2">
                    <span class="bg-blue-50 text-blue-700 rounded-full px-3 py-1">{{ $act->category->name }}</span>
                    @if($participation->isPending())
                        <span class="bg-amber-50 text-amber-700 rounded-full px-3 py-1">รออนุมัติ</span>
                    @elseif($participation->isApproved())
                        <span class="bg-emerald-50 text-emerald-700 rounded-full px-3 py-1">เข้าร่วมแล้ว</span>
                    @elseif($participation->isRejected())
                        <span class="bg-rose-50 text-rose-700 rounded-full px-3 py-1">ถูกปฏิเสธ</span>
                    @else
                        <span class="bg-slate-100 text-slate-600 rounded-full px-3 py-1">ถอนตัวแล้ว</span>
                    @endif
                </div>
                <h3 class="text-lg font-semibold break-words"><a href="{{ route('activities.show', $act) }}" class="hover:text-blue-600">{{ $act->title }}</a></h3>
                <p class="text-sm text-slate-500 mt-1">โดย {{ $act->user->name }} · {{ $act->starts_at->format('d/m/Y H:i') }}</p>

                @if(in_array($participation->status, ['pending', 'approved']))
                <div class="mt-4 pt-3 border-t">
                    <form method="POST" action="{{ route('activities.cancel-request', $act) }}" onsubmit="return confirm('{{ $participation->isApproved() ? 'ยืนยันถอนตัวจากกิจกรรม?' : 'ยืนยันถอนคำขอ?' }}')">
                        @csrf @method('PATCH')
                        <button class="text-sm bg-rose-50 text-rose-700 border border-rose-200 rounded-xl px-4 py-2 hover:bg-rose-100 transition-colors">
                            {{ $participation->isApproved() ? 'ถอนตัว' : 'ถอนคำขอ' }}
                        </button>
                    </form>
                </div>
                @endif
            </article>
            @endforeach
        </div>
        @else
        <div class="border border-dashed rounded-2xl p-10 text-center text-slate-500">คุณยังไม่ได้ขอเข้าร่วมกิจกรรมใด · <a href="{{ route('activities.index') }}" class="text-blue-600">ค้นหากิจกรรม</a></div>
        @endif
    </section>
</div>
@endsection
