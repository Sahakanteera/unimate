{{-- หัวข้อหน้าผู้ดูแลระบบ + แท็บสลับ 4 หน้า admin ใช้: @include('admin.partials.header', ['title' => '...', 'description' => '...']) --}}
@php
    $pendingReportCount = \App\Models\Report::where('status', 'pending')->count();
    $adminTabs = [
        ['admin.insights.index', 'admin.insights.*', 'ภาพรวมข้อมูล', 'ภาพรวม', 'chart-bar'],
        ['admin.users.index', 'admin.users.*', 'จัดการผู้ใช้งาน', 'ผู้ใช้', 'users'],
        ['admin.categories.index', 'admin.categories.*', 'จัดการหมวดหมู่', 'หมวดหมู่', 'squares'],
        ['admin.reports.index', 'admin.reports.*', 'ตรวจรายงาน', 'รายงาน', 'flag'],
    ];
@endphp
<div>
    <h1 class="page-title">{{ $title }}</h1>
    <p class="mt-2 max-w-2xl text-ink-muted">{{ $description }}</p>
    <nav class="tabs mt-6" aria-label="เมนูผู้ดูแลระบบ">
        @foreach($adminTabs as [$route, $pattern, $label, $short, $icon])
            <a href="{{ route($route) }}" @if(request()->routeIs($pattern)) aria-current="page" @endif class="tab {{ request()->routeIs($pattern) ? 'tab-active' : '' }}">
                <x-ui.icon :name="$icon" class="h-4 w-4" /> <span class="sm:hidden">{{ $short }}</span><span class="hidden sm:inline">{{ $label }}</span>
                @if($route === 'admin.reports.index' && $pendingReportCount > 0)<span class="badge-count">{{ $pendingReportCount }}</span>@endif
            </a>
        @endforeach
    </nav>
</div>
