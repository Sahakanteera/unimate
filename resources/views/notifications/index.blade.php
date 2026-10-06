@extends('layouts.app')
@section('title', 'การแจ้งเตือน | UniMate')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">การแจ้งเตือน</h1>
            <p class="text-slate-500 text-sm mt-1">ประวัติการแจ้งเตือนสถานะคำขอเข้าร่วมกิจกรรมของคุณ</p>
        </div>
        @if(auth()->user()->unreadNotifications->count() > 0)
        <form action="{{ route('notifications.markAsRead') }}" method="POST">
            @csrf
            <button type="submit" class="text-sm bg-blue-50 text-blue-600 border border-blue-200 rounded-xl px-4 py-2 hover:bg-blue-100 transition-colors">
                อ่านทั้งหมดแล้ว
            </button>
        </form>
        @endif
    </div>

    <div class="bg-white border rounded-2xl shadow-sm divide-y">
        @forelse(auth()->user()->notifications as $notification)
        <div class="p-4 sm:p-5 flex items-start gap-4 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50/50' }}">
            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            </div>
            <div class="flex-grow min-w-0">
                <p class="text-slate-800 text-sm font-medium">{{ $notification->data['message'] ?? 'มีการแจ้งเตือนใหม่' }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
            </div>
            @if(!$notification->read_at)
            <span class="w-2.5 h-2.5 rounded-full bg-blue-600 flex-shrink-0 self-center"></span>
            @endif
        </div>
        @empty
        <div class="p-12 text-center text-slate-500">
            ไม่มีการแจ้งเตือนในขณะนี้
        </div>
        @endforelse
    </div>
</div>
@endsection