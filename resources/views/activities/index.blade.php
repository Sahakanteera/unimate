@extends('layouts.app')
@section('title', 'ค้นหากิจกรรม | UniMate')
@section('content')
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <div><h1 class="text-3xl font-bold">หาเพื่อนร่วมกิจกรรม</h1><p class="mt-2 text-slate-500">ค้นหากิจกรรมที่สนใจ แล้วดูรายละเอียดการนัดหมาย</p></div>
    <a href="{{ route('activities.create') }}" class="rounded-xl bg-blue-600 text-white px-5 py-3">+ สร้างโพสต์กิจกรรม</a>
</div>
@include('activities.errors')
<form method="GET" action="{{ route('activities.index') }}" class="bg-white border rounded-2xl p-5 grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <label>คำค้น<input name="q" value="{{ request('q') }}" placeholder="ชื่อหรือรายละเอียดกิจกรรม" class="block w-full border rounded-lg p-2 mt-1" maxlength="200"></label>
    <label>หมวดหมู่<select name="category_id" class="block w-full border rounded-lg p-2 mt-1"><option value="">ทุกหมวดหมู่</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
    <label>สถานที่<input name="location" value="{{ request('location') }}" class="block w-full border rounded-lg p-2 mt-1" placeholder="เช่น สนามกีฬา" maxlength="255"></label>
    <label>วันที่เริ่มกิจกรรม<input type="date" name="date" value="{{ request('date') }}" class="block w-full border rounded-lg p-2 mt-1"></label>
    <label>สถานะ<select name="status" class="block w-full border rounded-lg p-2 mt-1"><option value="published" @selected(request('status', 'published') === 'published')>ประกาศแล้ว</option><option value="cancelled" @selected(request('status') === 'cancelled')>ยกเลิกแล้ว</option><option value="all" @selected(request('status') === 'all')>ทั้งหมด</option></select></label>
    <div class="flex items-end gap-3"><button class="bg-blue-600 text-white rounded-lg px-5 py-2">ค้นหา</button><a href="{{ route('activities.index') }}" class="px-3 py-2 text-slate-600">ล้างตัวกรอง</a></div>
</form>
<p class="mb-4 text-slate-500">พบ {{ $activities->total() }} กิจกรรม</p>
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
@forelse($activities as $activity)
    <article class="bg-white border rounded-2xl p-6 shadow-sm">
        <div class="flex flex-wrap gap-2 text-sm mb-3"><span class="bg-blue-50 text-blue-700 rounded-full px-3 py-1">{{ $activity->category->name }}</span><span class="rounded-full px-3 py-1 {{ $activity->status === 'cancelled' ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $activity->status === 'cancelled' ? 'ยกเลิกแล้ว' : 'ประกาศแล้ว' }}</span></div>
        <h2 class="text-xl font-semibold break-words"><a href="{{ route('activities.show', $activity) }}" class="hover:text-blue-600">{{ $activity->title }}</a></h2>
        <p class="text-slate-500 mt-2 break-words">{{ Str::limit($activity->description, 120) }}</p>
        <div class="space-y-2 mt-5 text-sm"><p>วันเริ่ม: {{ $activity->starts_at->format('d/m/Y H:i') }}</p><p class="break-words">สถานที่: {{ $activity->location }}</p><p>ผู้เข้าร่วม: {{ $activity->approved_participants_count ?? $activity->approvedCount() }}/{{ $activity->capacity }} คน</p><p>ผู้ประกาศ: {{ $activity->user->name }}</p>@if($activity->reviews_count > 0)<p class="text-amber-500">★ {{ number_format($activity->reviews_avg_rating, 1) }} <span class="text-slate-500">({{ $activity->reviews_count }} รีวิว)</span></p>@endif</div>
        <a href="{{ route('activities.show', $activity) }}" class="inline-block mt-5 text-blue-600 font-medium">ดูรายละเอียด →</a>
    </article>
@empty
    <div class="col-span-full border border-dashed rounded-2xl p-12 text-center text-slate-500">ไม่พบกิจกรรมที่ตรงกับตัวกรอง ลองเปลี่ยนคำค้นหรือสร้างกิจกรรมใหม่</div>
@endforelse
</div>
<div class="mt-6">{{ $activities->links() }}</div>
@endsection
