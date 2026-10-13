{{--
    วันเวลาแบบไทยในแท็ก <time> (ชี้เมาส์เห็นวันเวลาเต็ม)
    ใช้: <x-ui.time :value="$review->created_at" /> · mode="relative" (เช่น 2 ชั่วโมงที่แล้ว) · mode="date"
--}}
@props(['value', 'mode' => 'short'])
@php
    $local = $value->copy()->locale('th');
    $text = match ($mode) {
        'relative' => $local->diffForHumans(),
        'date' => $local->isoFormat('D MMM YYYY'),
        default => $local->isoFormat('D MMM YYYY').' · '.$local->format('H:i'),
    };
@endphp
<time datetime="{{ $value->toIso8601String() }}" title="{{ $local->isoFormat('dd D MMMM YYYY') }} {{ $local->format('H:i') }}" {{ $attributes }}>{{ $text }}</time>
