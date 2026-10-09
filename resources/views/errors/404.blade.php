@extends('layouts.app')
@section('title', 'ไม่พบหน้านี้ | UniMate')
@section('content')
<div class="flex min-h-[60vh] items-center justify-center py-8">
    <div class="card w-full max-w-lg p-8 text-center sm:p-12">
        <x-ui.empty-art name="error-404" size="lg" />
        <span class="chip chip-outline mt-4">404</span>
        <h1 class="mt-4 text-[1.75rem] font-medium leading-tight tracking-tight text-ink-soft sm:text-4xl">ไม่พบหน้าที่คุณกำลังหา</h1>
        <p class="mt-3 text-ink-muted">ลิงก์อาจเปลี่ยนไป หรือหน้านี้ไม่มีอยู่แล้ว ลองกลับไปเริ่มที่หน้าแรก</p>
        <a href="{{ route('home') }}" class="btn btn-primary mt-8"><x-ui.icon name="arrow-left" class="h-4 w-4" /> กลับหน้าแรก</a>
    </div>
</div>
@endsection
