@extends('layouts.app')
@section('title', 'การแจ้งเตือน | UniMate')
@section('content')
<div class="mx-auto max-w-3xl">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">การแจ้งเตือน</h1>
            <p class="mt-2 text-sm text-ink-muted">ประวัติการแจ้งเตือนสถานะคำขอเข้าร่วมกิจกรรมของคุณ</p>
        </div>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifications.markAsRead') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm"><x-ui.icon name="check" class="h-4 w-4" /> อ่านทั้งหมดแล้ว</button>
            </form>
        @endif
    </div>

    <ul class="card mt-6 divide-y divide-line overflow-hidden">
        @forelse($notifications as $notification)
            @php
                $message = $notification->data['message'] ?? 'มีการแจ้งเตือนใหม่';
                [$icon, $tone] = match (true) {
                    str_contains($message, 'ไม่ได้รับการอนุมัติ') => ['x', 'bg-tint-coral'],
                    str_contains($message, 'อนุมัติแล้ว') => ['check', 'bg-tint-mint'],
                    default => ['bell', 'bg-tint-aqua'],
                };
            @endphp
            <li class="flex items-start gap-4 p-4 sm:p-5 {{ $notification->read_at ? '' : 'bg-brand-50/60' }}">
                <span class="mt-0.5 grid h-10 w-10 shrink-0 place-items-center rounded-full text-ink {{ $tone }}"><x-ui.icon :name="$icon" /></span>
                <div class="min-w-0 flex-1">
                    <p class="break-anywhere text-sm leading-relaxed text-ink {{ $notification->read_at ? '' : 'font-medium' }}">{{ $message }}</p>
                    <p class="mt-1 text-xs text-ink-muted"><x-ui.time :value="$notification->created_at" mode="relative" /></p>
                </div>
                @if(!$notification->read_at)
                    <span class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full bg-brand-600" aria-label="ยังไม่อ่าน"></span>
                @endif
            </li>
        @empty
            <li class="flex flex-col items-center px-6 py-14 text-center">
                <span class="grid h-14 w-14 place-items-center rounded-2xl bg-canvas text-ink-muted"><x-ui.icon name="bell" class="h-6 w-6" /></span>
                <p class="mt-4 text-ink-muted">ไม่มีการแจ้งเตือนในขณะนี้</p>
            </li>
        @endforelse
    </ul>
</div>
@endsection
