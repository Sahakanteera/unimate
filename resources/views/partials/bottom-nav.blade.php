{{-- เมนูล่างบนมือถือ: แคปซูลลอยแบบเดียวกับ header กดง่ายด้วยนิ้วโป้ง (แทนแถบเมนูเลื่อนข้างแบบเดิม) --}}
@php
    $tabs = [
        ['activities.index', 'กิจกรรม', 'home', ['activities.index', 'activities.show', 'activities.edit']],
        ['my-activities.index', 'นัดของฉัน', 'calendar', ['my-activities.*', 'activities.requests']],
    ];
@endphp
<nav class="fixed inset-x-3 bottom-3 z-40 lg:hidden" aria-label="เมนูหลัก (มือถือ)">
    <div class="mx-auto flex h-[68px] max-w-md items-center justify-around rounded-full border border-line/80 bg-white/85 px-3 shadow-float backdrop-blur-xl">
        @php [$route, $label, $icon, $patterns] = $tabs[0]; $active = request()->routeIs(...$patterns); @endphp
        <a href="{{ route($route) }}" @if($active) aria-current="page" @endif class="flex w-20 flex-col items-center gap-1 rounded-full py-1.5 text-[11px] {{ $active ? 'font-medium text-ink' : 'text-ink-muted' }}">
            <x-ui.icon :name="$icon" class="h-6 w-6" />{{ $label }}
        </a>

        <a href="{{ route('activities.create') }}" class="grid h-12 w-12 place-items-center rounded-full bg-night text-white shadow-lift transition active:scale-95 {{ request()->routeIs('activities.create') ? 'ring-4 ring-brand-600/25' : '' }}" aria-label="สร้างโพสต์">
            <x-ui.icon name="plus" class="h-6 w-6" />
        </a>

        @php [$route, $label, $icon, $patterns] = $tabs[1]; $active = request()->routeIs(...$patterns); @endphp
        <a href="{{ route($route) }}" @if($active) aria-current="page" @endif class="flex w-20 flex-col items-center gap-1 rounded-full py-1.5 text-[11px] {{ $active ? 'font-medium text-ink' : 'text-ink-muted' }}">
            <x-ui.icon :name="$icon" class="h-6 w-6" />{{ $label }}
        </a>
    </div>
</nav>
