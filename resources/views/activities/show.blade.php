@extends('layouts.app')
@section('title', $activity->title.' | UniMate')
@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('activities.index') }}" class="text-blue-600">← กลับหน้ากิจกรรม</a>
    <article class="mt-5 bg-white border rounded-2xl p-6 sm:p-8">
        @if($activity->status === 'cancelled')<div role="status" class="bg-rose-50 text-rose-700 border border-rose-200 rounded-xl p-4 mb-5">กิจกรรมนี้ถูกยกเลิกแล้ว</div>@endif
        <p class="text-blue-600 mb-2">{{ $activity->category->name }}</p>
        <h1 class="text-3xl font-bold break-words">{{ $activity->title }}</h1>
        <p class="text-slate-500 mt-3">ประกาศโดย {{ $activity->user->name }} · อัปเดต {{ $activity->updated_at->format('d/m/Y H:i') }}</p>
        <dl class="grid sm:grid-cols-2 gap-5 bg-slate-50 rounded-xl p-5 my-6">
            <div><dt class="text-slate-500">เวลาเริ่ม (เวลาไทย)</dt><dd>{{ $activity->starts_at->format('d/m/Y H:i') }}</dd></div>
            <div><dt class="text-slate-500">เวลาสิ้นสุด (เวลาไทย)</dt><dd>{{ $activity->ends_at->format('d/m/Y H:i') }}</dd></div>
            <div><dt class="text-slate-500">สถานที่</dt><dd class="break-words">{{ $activity->location }}</dd></div>
            <div><dt class="text-slate-500">จำนวนคนที่รับ (ไม่รวมผู้ประกาศ)</dt><dd>{{ $activity->capacity }} คน</dd></div>
        </dl>
        <h2 class="font-semibold text-xl mb-3">รายละเอียดกิจกรรม</h2>
        <p class="whitespace-pre-wrap break-words leading-relaxed">{{ $activity->description }}</p>
        {{-- ซ่อนปุ่มตาม Policy; ฝั่ง Controller/FormRequest ยังตรวจสิทธิ์ทุกคำขอด้วย --}}
        @can('update', $activity)
            @if($activity->status !== 'cancelled')
            <div class="flex flex-wrap gap-4 mt-8 border-t pt-6">
                <a href="{{ route('activities.edit', $activity) }}" class="bg-blue-600 text-white rounded-xl px-5 py-3">แก้ไขกิจกรรม</a>
                <form method="POST" action="{{ route('activities.cancel', $activity) }}" onsubmit="return confirm('ยืนยันยกเลิกกิจกรรม? เมื่อยกเลิกแล้วจะไม่สามารถแก้ไขได้')">@csrf @method('PATCH')<button class="bg-rose-50 text-rose-700 rounded-xl px-5 py-3">ยกเลิกกิจกรรม</button></form>
            </div>
            @endif
        @else
            <p class="mt-8 pt-5 border-t text-sm text-slate-500">เฉพาะเจ้าของโพสต์เท่านั้นที่แก้ไขหรือยกเลิกกิจกรรมนี้ได้</p>
        @endcan
    </article>
</div>
@endsection
