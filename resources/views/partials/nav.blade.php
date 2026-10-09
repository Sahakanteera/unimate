{{-- แถบเมนูบนแบบแคปซูลลอย (ประยุกต์จาก header ของ fastwork) --}}
@php
    $authUser = Auth::user();
    $isAdmin = $authUser?->isAdmin() ?? false;
    $unreadCount = $authUser ? $authUser->unreadNotifications()->count() : 0;
    $pendingReports = $isAdmin ? \App\Models\Report::where('status', 'pending')->count() : 0;
    // เมนูหลัก: [route, ชื่อ, pattern สำหรับไฮไลต์หน้าปัจจุบัน] ปุ่ม "สร้างโพสต์" แยกเป็นปุ่มหลักด้านขวา
    // การเช็กชื่อทำในหน้า "จัดการคำขอ" ของผู้จัด จึงไม่มีเมนูแยก
    $mainNav = [
        ['activities.index', 'กิจกรรม', ['activities.index', 'activities.show', 'activities.edit']],
        ['my-activities.index', 'นัดของฉัน', ['my-activities.*', 'activities.requests']],
    ];
    $adminNav = [
        ['admin.users.index', 'จัดการผู้ใช้งาน', 'admin.users.*', 'users'],
        ['admin.categories.index', 'จัดการหมวดหมู่', 'admin.categories.*', 'squares'],
        ['admin.reports.index', 'ตรวจรายงาน', 'admin.reports.*', 'flag'],
    ];
@endphp
<header class="sticky top-0 z-40 px-3 pt-3 sm:px-4">
    <div class="mx-auto flex h-16 max-w-[76rem] items-center gap-2 rounded-full border border-line/80 bg-white/80 pl-4 pr-2 shadow-soft backdrop-blur-md">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5 rounded-full pr-2" aria-label="UniMate หน้าแรก">
            <x-ui.brand-mark class="h-9 w-9" />
            <span class="text-lg font-medium tracking-tight text-ink sm:text-xl">UniMate</span>
        </a>

        @auth
            <nav class="ml-3 hidden items-center gap-1 lg:flex" aria-label="เมนูหลัก">
                @foreach($mainNav as [$route, $label, $patterns])
                    @php $active = request()->routeIs(...$patterns); @endphp
                    <a href="{{ route($route) }}" @if($active) aria-current="page" @endif
                       class="inline-flex h-10 items-center rounded-full px-4 text-[0.9375rem] transition {{ $active ? 'bg-canvas font-medium text-ink' : 'text-ink-muted hover:bg-canvas/70 hover:text-ink' }}">{{ $label }}</a>
                @endforeach

                {{-- รวมเมนู Admin เป็น dropdown เดียว เพื่อไม่ให้แถบเมนูยาวเกิน --}}
                @if($isAdmin)
                    <details class="relative" data-nav-dropdown>
                        <summary class="inline-flex h-10 cursor-pointer select-none items-center gap-1.5 rounded-full px-4 text-[0.9375rem] transition {{ request()->routeIs('admin.*') ? 'bg-canvas font-medium text-ink' : 'text-ink-muted hover:bg-canvas/70 hover:text-ink' }}">
                            <x-ui.icon name="shield" class="h-4 w-4" />
                            ผู้ดูแลระบบ
                            @if($pendingReports > 0)<span class="badge-count">{{ $pendingReports }}</span>@endif
                            <x-ui.icon name="chevron-down" class="h-4 w-4" />
                        </summary>
                        <div class="absolute left-0 mt-2 w-60 animate-drop-in rounded-2xl border border-line bg-white p-1.5 shadow-float">
                            @foreach($adminNav as [$route, $label, $pattern, $icon])
                                <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition hover:bg-canvas {{ request()->routeIs($pattern) ? 'font-medium text-ink' : 'text-ink-soft' }}">
                                    <x-ui.icon :name="$icon" class="h-4 w-4 text-ink-faint" />
                                    <span class="flex-1">{{ $label }}</span>
                                    @if($route === 'admin.reports.index' && $pendingReports > 0)<span class="badge-count">{{ $pendingReports }}</span>@endif
                                </a>
                            @endforeach
                        </div>
                    </details>
                @endif
            </nav>
        @endauth

        <div class="ml-auto flex items-center gap-1 sm:gap-1.5">
            @auth
                <a href="{{ route('activities.create') }}" class="btn btn-primary mr-1 hidden lg:inline-flex">
                    <x-ui.icon name="plus" class="h-4 w-4" />
                    สร้างโพสต์
                </a>

                {{-- ปุ่มกระดิ่งแจ้งเตือนพร้อม Badge นับจำนวน --}}
                <a href="{{ route('notifications.index') }}" class="btn btn-ghost btn-icon relative {{ request()->routeIs('notifications.*') ? 'bg-canvas text-ink' : '' }}" title="การแจ้งเตือน" aria-label="การแจ้งเตือน{{ $unreadCount > 0 ? ' ('.$unreadCount.' ยังไม่อ่าน)' : '' }}">
                    <x-ui.icon name="bell" class="h-[22px] w-[22px]" />
                    @if($unreadCount > 0)
                        <span class="badge-count absolute right-0.5 top-0.5 h-[18px] min-w-[18px] text-[0.625rem] ring-2 ring-white">{{ $unreadCount }}</span>
                    @endif
                </a>

                {{-- เมนูบัญชี: ชื่อ บทบาท ลิงก์โปรไฟล์ และออกจากระบบ --}}
                <details class="relative" data-nav-dropdown>
                    <summary class="flex cursor-pointer select-none items-center gap-2 rounded-full p-1 pr-1.5 transition hover:bg-canvas sm:pr-3" aria-label="เมนูบัญชีของ {{ $authUser->name }}">
                        <x-ui.avatar :user="$authUser" size="md" />
                        <span class="hidden min-w-0 text-left sm:block">
                            <span class="block max-w-[9rem] truncate text-sm font-medium leading-tight text-ink" title="{{ $authUser->name }}">{{ $authUser->name }}</span>
                            <span class="block text-xs leading-tight text-ink-muted">{{ $isAdmin ? 'ผู้ดูแลระบบ' : ($authUser->student_id ?? 'นักศึกษา') }}</span>
                        </span>
                        <x-ui.icon name="chevron-down" class="hidden h-4 w-4 text-ink-faint sm:block" />
                    </summary>
                    <div class="absolute right-0 mt-2 w-72 animate-drop-in rounded-2xl border border-line bg-white p-1.5 shadow-float">
                        <div class="flex items-center gap-3 rounded-xl px-3 py-3">
                            <x-ui.avatar :user="$authUser" size="lg" />
                            <div class="min-w-0">
                                <p class="truncate font-medium text-ink">{{ $authUser->name }}</p>
                                <p class="truncate text-xs text-ink-muted">{{ $authUser->email }}</p>
                                <span class="chip {{ $isAdmin ? 'chip-warn' : 'chip-info' }} mt-1.5 h-6 px-2.5 text-xs">{{ $isAdmin ? 'ผู้ดูแลระบบ' : 'นักศึกษา' }}</span>
                            </div>
                        </div>
                        <div class="my-1 h-px bg-line"></div>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-ink-soft transition hover:bg-canvas">
                            <x-ui.icon name="user-circle" class="h-4 w-4 text-ink-faint" /> โปรไฟล์ของฉัน
                        </a>
                        <a href="{{ route('my-activities.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-ink-soft transition hover:bg-canvas">
                            <x-ui.icon name="calendar" class="h-4 w-4 text-ink-faint" /> นัดของฉัน
                        </a>
                        @if($isAdmin)
                            <div class="my-1 h-px bg-line lg:hidden"></div>
                            @foreach($adminNav as [$route, $label, $pattern, $icon])
                                <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-ink-soft transition hover:bg-canvas lg:hidden">
                                    <x-ui.icon :name="$icon" class="h-4 w-4 text-ink-faint" />
                                    <span class="flex-1">{{ $label }}</span>
                                    @if($route === 'admin.reports.index' && $pendingReports > 0)<span class="badge-count">{{ $pendingReports }}</span>@endif
                                </a>
                            @endforeach
                        @endif
                        <div class="my-1 h-px bg-line"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-bad transition hover:bg-bad-soft">
                                <x-ui.icon name="logout" class="h-4 w-4" /> ออกจากระบบ
                            </button>
                        </form>
                    </div>
                </details>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost h-9 px-3 text-sm sm:h-11 sm:px-5 sm:text-[0.9375rem] {{ request()->routeIs('login') ? 'text-ink' : '' }}">เข้าสู่ระบบ</a>
                <a href="{{ route('register') }}" class="btn btn-primary h-9 px-3.5 text-sm sm:h-11 sm:px-5 sm:text-[0.9375rem]">สมัครสมาชิก</a>
            @endauth
        </div>
    </div>
</header>
