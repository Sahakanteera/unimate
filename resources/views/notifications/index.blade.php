@extends('layouts.app')
@section('title', 'การแจ้งเตือน | UniMate')
@section('content')
<div class="mx-auto max-w-3xl">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">การแจ้งเตือน</h1>
            <p class="mt-2 text-sm text-ink-muted">คำขอเข้าร่วม ความเคลื่อนไหวของกิจกรรม รีวิว และผลการตรวจรายงาน</p>
        </div>
        @if($unreadCount > 0)
            <form action="{{ route('notifications.markAsRead') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm"><x-ui.icon name="check" class="h-4 w-4" /> อ่านทั้งหมดแล้ว ({{ $unreadCount }})</button>
            </form>
        @endif
    </div>

    <ul class="card mt-6 divide-y divide-line overflow-hidden">
        @forelse($notifications as $notification)
            @php
                $message = $notification->data['message'] ?? 'มีการแจ้งเตือนใหม่';
                // การแจ้งเตือนเก่าไม่มี kind ให้เดาจากข้อความเหมือนเดิม
                $kind = $notification->data['kind'] ?? match (true) {
                    str_contains($message, 'ไม่ได้รับการอนุมัติ') => 'danger',
                    str_contains($message, 'อนุมัติแล้ว') => 'success',
                    default => 'info',
                };
                [$icon, $tone] = match ($kind) {
                    'success' => ['check', 'bg-tint-mint'],
                    'danger' => ['x', 'bg-tint-coral'],
                    'warning' => ['alert', 'bg-tint-butter'],
                    'request' => ['users', 'bg-tint-lilac'],
                    'review' => ['star', 'bg-tint-peach'],
                    default => ['bell', 'bg-tint-aqua'],
                };
                $hasLink = ! empty($notification->data['url']);
            @endphp
            <li class="{{ $notification->read_at ? '' : 'bg-brand-50/60' }}">
                {{-- กดแล้วทำเครื่องหมายว่าอ่าน และพาไปหน้าที่เกี่ยวข้อง (ถ้ามี) --}}
                <a href="{{ route('notifications.open', $notification->id) }}" class="flex items-start gap-4 p-4 transition hover:bg-canvas sm:p-5">
                    <span class="mt-0.5 grid h-10 w-10 shrink-0 place-items-center rounded-full text-ink {{ $tone }}"><x-ui.icon :name="$icon" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="break-anywhere text-sm leading-relaxed text-ink {{ $notification->read_at ? '' : 'font-medium' }}">{{ $message }}</p>
                        <p class="mt-1 text-xs text-ink-muted"><x-ui.time :value="$notification->created_at" mode="relative" /></p>
                    </div>
                    @if(!$notification->read_at)
                        <span class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full bg-brand-600" aria-label="ยังไม่อ่าน"></span>
                    @elseif($hasLink)
                        <x-ui.icon name="chevron-right" class="mt-2 h-4 w-4 text-ink-faint" />
                    @endif
                </a>
            </li>
        @empty
            <li class="flex flex-col items-center px-6 py-14 text-center">
                <x-ui.empty-art name="empty-notifications" />
                <p class="mt-4 text-ink-muted">ไม่มีการแจ้งเตือนในขณะนี้</p>
            </li>
        @endforelse
    </ul>

    <div class="mt-6">{{ $notifications->links('partials.pagination') }}</div>
</div>
@endsection
