{{-- ช่องวันที่แบบปฏิทิน (วัน/วันที่/เดือน ภาษาไทย) ใช้: <x-ui.date-tile :date="$activity->starts_at" tint="bg-tint-mint" /> --}}
@props(['date', 'tint' => 'bg-canvas'])
@php $local = $date->copy()->locale('th'); @endphp
<div {{ $attributes->class(['flex w-14 shrink-0 flex-col items-center rounded-tile py-2 text-ink', $tint]) }}>
    <span class="text-[11px] font-medium leading-none text-ink/70">{{ $local->isoFormat('dd') }}</span>
    <span class="mt-1 text-2xl font-medium leading-none tracking-tight">{{ $local->format('j') }}</span>
    <span class="mt-1 text-[11px] font-medium leading-none text-ink/70">{{ $local->isoFormat('MMM') }}</span>
</div>
