@extends('layouts.app')
@section('title', 'จัดการหมวดหมู่ | UniMate')
@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold mb-3">จัดการหมวดหมู่กิจกรรม</h1>
    <p class="text-slate-500 mb-6">เพิ่มหรือเปลี่ยนชื่อหมวดหมู่ ลบได้เฉพาะหมวดหมู่ที่ยังไม่มีกิจกรรม</p>
    @include('activities.errors')
    <form method="POST" action="{{ route('admin.categories.store') }}" class="flex flex-wrap gap-3 bg-white border rounded-xl p-5 mb-6">@csrf<label class="flex-1">ชื่อหมวดหมู่ใหม่<input required name="name" maxlength="100" value="{{ old('name') }}" class="block w-full border rounded-lg p-2 mt-1"></label><button class="self-end bg-blue-600 text-white rounded-lg px-4 py-2">เพิ่มหมวดหมู่</button></form>
    <div class="space-y-3">
    @forelse($categories as $category)
        <div class="bg-white border rounded-xl p-5">
            <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="flex flex-wrap items-end gap-3">@csrf @method('PUT')<label class="flex-1">ชื่อหมวดหมู่<input required name="name" maxlength="100" value="{{ $category->name }}" class="block w-full border rounded-lg p-2 mt-1"></label><button class="bg-slate-100 rounded-lg px-4 py-2">บันทึกชื่อ</button></form>
            <div class="flex justify-between items-center mt-3"><p class="text-sm text-slate-500">ใช้งาน {{ $category->activities_count }} กิจกรรม</p><form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('ยืนยันลบหมวดหมู่?')">@csrf @method('DELETE')<button @disabled($category->activities_count > 0) class="text-rose-600 disabled:text-slate-400">ลบหมวดหมู่</button></form></div>
        </div>
    @empty
        <p class="text-slate-500">ยังไม่มีหมวดหมู่</p>
    @endforelse
    </div>
</div>
@endsection
