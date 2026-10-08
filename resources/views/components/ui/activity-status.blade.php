{{-- ป้ายสถานะกิจกรรม ใช้: <x-ui.activity-status :state="App\Support\Ui::activityState($activity, $count)" :remaining="3" /> --}}
@props(['state', 'remaining' => null])
@switch($state)
    @case('cancelled')
        <span {{ $attributes->class('chip chip-bad') }}>ยกเลิกแล้ว</span>
        @break
    @case('ended')
        <span {{ $attributes->class('chip') }}>จบแล้ว</span>
        @break
    @case('ongoing')
        <span {{ $attributes->class('chip chip-info') }}><span class="h-1.5 w-1.5 rounded-full bg-brand-600 motion-safe:animate-pulse"></span>กำลังดำเนินการ</span>
        @break
    @case('full')
        <span {{ $attributes->class('chip chip-warn') }}>เต็มแล้ว</span>
        @break
    @default
        <span {{ $attributes->class('chip chip-ok') }}>เปิดรับ{{ $remaining !== null ? ' · ว่าง '.$remaining.' ที่' : '' }}</span>
@endswitch
