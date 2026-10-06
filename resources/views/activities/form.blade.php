@extends('layouts.app')
@section('title', ($activity->exists ? 'แก้ไขกิจกรรม' : 'สร้างโพสต์กิจกรรม').' | UniMate')
@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('activities.index') }}" class="text-blue-600">← กลับหน้ากิจกรรม</a>
    <h1 class="text-3xl font-bold my-6">{{ $activity->exists ? 'แก้ไขกิจกรรม' : 'สร้างโพสต์หาคนร่วมกิจกรรม' }}</h1>
    @include('activities.errors')
    @if($categories->isEmpty())<p class="p-4 bg-amber-50 text-amber-800 rounded-xl mb-4">ยังไม่มีหมวดหมู่ กรุณาให้ผู้ดูแลระบบเพิ่มหมวดหมู่ก่อนสร้างกิจกรรม</p>@endif
    <form method="POST" action="{{ $activity->exists ? route('activities.update', $activity) : route('activities.store') }}" class="bg-white border rounded-2xl p-6 space-y-5">
        @csrf
        @if($activity->exists) @method('PUT') @endif
        <label class="block">ชื่อกิจกรรม<input required name="title" maxlength="200" value="{{ old('title', $activity->title) }}" class="block w-full border rounded-lg p-3 mt-1"></label>
        <label class="block">หมวดหมู่<select required name="category_id" class="block w-full border rounded-lg p-3 mt-1"><option value="">เลือกหมวดหมู่</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $activity->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
        <label class="block">รายละเอียด<textarea required name="description" rows="6" maxlength="10000" class="block w-full border rounded-lg p-3 mt-1" placeholder="ทำอะไร นัดพบตรงไหน และควรเตรียมอะไรบ้าง">{{ old('description', $activity->description) }}</textarea></label>
        <label class="block">สถานที่<input required name="location" maxlength="255" value="{{ old('location', $activity->location) }}" class="block w-full border rounded-lg p-3 mt-1"></label>
        <p class="text-sm text-slate-500">เวลาไทย (Asia/Bangkok) · เวลาเริ่มต้องอยู่ในอนาคต และเวลาสิ้นสุดต้องอยู่หลังเวลาเริ่ม</p>
        <div class="grid sm:grid-cols-2 gap-5">
            <label>เวลาเริ่ม<input required type="datetime-local" name="starts_at" value="{{ old('starts_at', $activity->starts_at?->format('Y-m-d\TH:i')) }}" class="block w-full border rounded-lg p-3 mt-1"></label>
            <label>เวลาสิ้นสุด<input required type="datetime-local" name="ends_at" value="{{ old('ends_at', $activity->ends_at?->format('Y-m-d\TH:i')) }}" class="block w-full border rounded-lg p-3 mt-1"></label>
        </div>
        <label class="block">จำนวนคนที่รับ (ไม่รวมผู้ประกาศ)<input required type="number" min="1" max="10000" name="capacity" value="{{ old('capacity', $activity->capacity) }}" class="block w-full border rounded-lg p-3 mt-1"></label>
        <div class="flex gap-4 items-center"><button @disabled($categories->isEmpty()) class="bg-blue-600 text-white rounded-xl px-6 py-3 disabled:opacity-50">{{ $activity->exists ? 'บันทึกการแก้ไข' : 'ประกาศกิจกรรม' }}</button><a href="{{ $activity->exists ? route('activities.show', $activity) : route('activities.index') }}" class="text-slate-500">กลับ</a></div>
    </form>
</div>
@endsection
