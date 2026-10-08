{{-- รูปโปรไฟล์วงกลม ถ้าไม่มีรูปจะแสดงอักษรย่อบนพื้นพาสเทล ใช้: <x-ui.avatar :user="$user" size="sm" /> --}}
@props(['user', 'size' => 'md'])
@php
    $sizes = [
        'xs' => 'h-6 w-6 text-[10px]',
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-14 w-14 text-base',
        'xl' => 'h-24 w-24 text-2xl',
    ];
    $tints = ['bg-tint-mint', 'bg-tint-aqua', 'bg-tint-lilac', 'bg-tint-coral', 'bg-tint-butter', 'bg-tint-peach'];
    $hasPhoto = $user->avatar && Storage::disk('public')->exists($user->avatar);
@endphp
<span {{ $attributes->class([
    'relative inline-grid shrink-0 place-items-center overflow-hidden rounded-full font-semibold text-ink-soft',
    $sizes[$size] ?? $sizes['md'],
    $tints[$user->id % count($tints)] => ! $hasPhoto,
]) }}>
    @if($hasPhoto)
        <img src="{{ asset('storage/'.$user->avatar) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
    @else
        <span aria-hidden="true">{{ $user->initials() }}</span>
    @endif
</span>
