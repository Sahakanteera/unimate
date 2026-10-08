@extends('layouts.app')

@section('title', '403 Forbidden - ไม่มีสิทธิ์เข้าถึง')

@section('content')
<div class="flex min-h-[60vh] items-center justify-center py-8">
    <div class="card w-full max-w-lg p-8 text-center sm:p-12">
        <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-tint-coral text-ink"><x-ui.icon name="lock" class="h-7 w-7" /></span>
        <span class="chip chip-bad mt-6">403 ACCESS DENIED</span>
        <h1 class="mt-4 text-[1.75rem] font-medium leading-tight tracking-tight text-ink-soft sm:text-4xl">คุณไม่มีสิทธิ์ดำเนินการนี้</h1>
        <p class="mx-auto mt-3 max-w-md text-ink-muted">
            กรุณาตรวจสอบสิทธิ์ของบัญชี การแก้ไขและยกเลิกกิจกรรมอนุญาตเฉพาะเจ้าของโพสต์ ส่วนหน้าจัดการระบบอนุญาตเฉพาะผู้ดูแลระบบ
        </p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            @auth
                <a href="{{ route('activities.index') }}" class="btn btn-primary">กลับหน้ากิจกรรม</a>
                <a href="{{ route('profile.edit') }}" class="btn btn-secondary">กลับสู่หน้าโปรไฟล์ของคุณ</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">เข้าสู่ระบบ</a>
            @endauth
        </div>
    </div>
</div>
@endsection
