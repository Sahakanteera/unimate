{{-- หัวข้อหน้าผู้ดูแลระบบ + แท็บสลับ 3 หน้า admin ใช้: @include('admin.partials.header', ['title' => '...', 'description' => '...']) --}}
@php
    $pendingReportCount = \App\Models\Report::where('status', 'pending')->count();
    $adminTabs = [
        ['admin.users.index', 'admin.users.*', 'จัดการผู้ใช้งาน', 'users'],
        ['admin.categories.index', 'admin.categories.*', 'จัดการหมวดหมู่', 'squares'],
        ['admin.reports.index', 'admin.reports.*', 'ตรวจรายงาน', 'flag'],
    ];
@endphp
<div>
    <span class="kicker"><x-ui.icon name="shield" class="h-4 w-4 text-warn" /> ผู้ดูแลระบบ</span>
    <h1 class="page-title mt-4">{{ $title }}</h1>
    <p class="mt-2 max-w-2xl text-ink-muted">{{ $description }}</p>
    <nav class="tabs mt-6" aria-label="เมนูผู้ดูแลระบบ">
        @foreach($adminTabs as [$route, $pattern, $label, $icon])
            <a href="{{ route($route) }}" @if(request()->routeIs($pattern)) aria-current="page" @endif class="tab {{ request()->routeIs($pattern) ? 'tab-active' : '' }}">
                <x-ui.icon :name="$icon" class="h-4 w-4" /> {{ $label }}
                @if($route === 'admin.reports.index' && $pendingReportCount > 0)<span class="badge-count">{{ $pendingReportCount }}</span>@endif
            </a>
        @endforeach
    </nav>
</div>
