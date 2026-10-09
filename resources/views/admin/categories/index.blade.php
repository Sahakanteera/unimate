@extends('layouts.app')
@section('title', 'จัดการหมวดหมู่ | UniMate')
@section('content')
<div class="space-y-6">
    @include('admin.partials.header', [
        'title' => 'จัดการหมวดหมู่กิจกรรม',
        'description' => 'เพิ่มหรือเปลี่ยนชื่อหมวดหมู่ ลบได้เฉพาะหมวดหมู่ที่ยังไม่มีกิจกรรม',
    ])

    @include('activities.errors')

    <div class="mx-auto max-w-3xl space-y-6">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="card flex flex-col gap-3 p-5 sm:flex-row sm:items-end">
            @csrf
            <div class="flex-1">
                <label for="new-category" class="field-label">ชื่อหมวดหมู่ใหม่</label>
                <input id="new-category" required name="name" maxlength="100" value="{{ old('name') }}" class="field" placeholder="เช่น ถ่ายภาพ">
            </div>
            <button class="btn btn-primary h-12"><x-ui.icon name="plus" class="h-4 w-4" /> เพิ่มหมวดหมู่</button>
        </form>

        <ul class="card divide-y divide-line">
            @forelse($categories as $category)
                <li class="flex flex-col gap-3 p-5 sm:flex-row sm:items-end">
                    <span class="hidden h-12 w-12 shrink-0 place-items-center rounded-tile sm:grid {{ App\Support\Ui::tint($category->id) }}"><x-ui.category-icon :category="$category->name" class="h-10 w-10" /></span>
                    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="flex flex-1 items-end gap-2">
                        @csrf @method('PUT')
                        <div class="flex-1">
                            <label for="category-{{ $category->id }}" class="field-label">ชื่อหมวดหมู่ <span class="font-normal text-ink-muted">· ใช้งาน {{ $category->activities_count }} กิจกรรม</span></label>
                            <input id="category-{{ $category->id }}" required name="name" maxlength="100" value="{{ $category->name }}" class="field">
                        </div>
                        <button class="btn btn-secondary h-12">บันทึกชื่อ</button>
                    </form>
                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('ยืนยันลบหมวดหมู่?')">
                        @csrf @method('DELETE')
                        <button @disabled($category->activities_count > 0) class="btn btn-ghost h-12 text-bad hover:bg-bad-soft hover:text-bad disabled:text-ink-faint" title="{{ $category->activities_count > 0 ? 'ลบไม่ได้เพราะมีกิจกรรมใช้งานอยู่' : 'ลบหมวดหมู่' }}">
                            <x-ui.icon name="trash" class="h-4 w-4" /> ลบหมวดหมู่
                        </button>
                    </form>
                </li>
            @empty
                <li class="p-10 text-center text-ink-muted">ยังไม่มีหมวดหมู่</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
