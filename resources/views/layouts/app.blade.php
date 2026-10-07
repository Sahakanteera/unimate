<!DOCTYPE html>
<html lang="th" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'UniMate - ระบบจับกลุ่มหาเพื่อนร่วมทำกิจกรรมในมหาวิทยาลัย')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Prompt', 'Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Prompt', 'Outfit', sans-serif;
        }
    </style>
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.03 10.03 0 013.122-.497c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m-1.782-1.782a3 3 0 11-4.243-4.243M3 3l18 18" />';
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
                }
            }
        }
    </script>
    @yield('styles')
</head>
<body class="h-full flex flex-col bg-slate-50 text-slate-800 antialiased">

    <header class="bg-slate-900 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                
                <div class="flex items-center space-x-3">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-amber-500 p-0.5 shadow-md group-hover:scale-105 transition-transform">
                            <div class="w-full h-full bg-slate-900 rounded-[10px] flex items-center justify-center">
                                <span class="text-xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-amber-400">U</span>
                            </div>
                        </div>
                        <span class="text-2xl font-bold tracking-tight text-white group-hover:text-blue-400 transition-colors">
                            Uni<span class="text-amber-400">Mate</span>
                        </span>
                    </a>

                    @auth
                    @php
                        // เมนูหลัก: [route, ชื่อ, pattern สำหรับไฮไลต์หน้าปัจจุบัน]
                        $mainNav = [
                            ['activities.index', 'กิจกรรม', ['activities.index', 'activities.show']],
                            ['activities.create', 'สร้างโพสต์', ['activities.create']],
                            ['my-activities.index', 'นัดของฉัน', ['my-activities.*', 'activities.requests']],
                            ['attendance.index', 'เช็กชื่อ', ['attendance.*']],
                        ];
                        $navLink = fn (array $patterns) => 'px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors '
                            .(request()->routeIs(...$patterns) ? 'bg-slate-800 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800');
                        $pendingReports = Auth::user()->isAdmin() ? \App\Models\Report::where('status', 'pending')->count() : 0;
                    @endphp
                    <nav class="hidden lg:flex items-center gap-1 ml-4 pl-4 border-l border-slate-800">
                        @foreach($mainNav as [$route, $label, $patterns])
                            <a href="{{ route($route) }}" class="{{ $navLink($patterns) }}">{{ $label }}</a>
                        @endforeach

                        {{-- รวมเมนู Admin เป็น dropdown เดียว เพื่อไม่ให้แถบเมนูยาวเกิน --}}
                        @if(Auth::user()->isAdmin())
                        <details class="relative" data-nav-dropdown>
                            <summary class="list-none cursor-pointer select-none flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('admin.*') ? 'bg-indigo-600 text-white' : 'bg-indigo-600/20 text-indigo-200 border border-indigo-500/30 hover:bg-indigo-600 hover:text-white' }}">
                                ผู้ดูแลระบบ
                                @if($pendingReports > 0)<span class="bg-rose-500 text-white text-[10px] font-bold rounded-full px-1.5 leading-4">{{ $pendingReports }}</span>@endif
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </summary>
                            <div class="absolute left-0 mt-2 w-56 bg-white text-slate-700 rounded-xl shadow-xl border py-2 z-50">
                                <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 text-sm hover:bg-slate-50 {{ request()->routeIs('admin.users.*') ? 'font-semibold text-indigo-600' : '' }}">จัดการผู้ใช้งาน</a>
                                <a href="{{ route('admin.categories.index') }}" class="block px-4 py-2 text-sm hover:bg-slate-50 {{ request()->routeIs('admin.categories.*') ? 'font-semibold text-indigo-600' : '' }}">จัดการหมวดหมู่</a>
                                <a href="{{ route('admin.reports.index') }}" class="flex items-center justify-between px-4 py-2 text-sm hover:bg-slate-50 {{ request()->routeIs('admin.reports.*') ? 'font-semibold text-indigo-600' : '' }}">
                                    ตรวจรายงาน
                                    @if($pendingReports > 0)<span class="bg-rose-500 text-white text-[10px] font-bold rounded-full px-1.5 leading-4">{{ $pendingReports }}</span>@endif
                                </a>
                            </div>
                        </details>
                        @endif
                    </nav>
                    @endauth
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    @auth
                        {{-- ปุ่มกระดิ่งแจ้งเตือนพร้อม Badge นับจำนวน --}}
                        <a href="{{ route('notifications.index') }}" class="relative p-2 text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition-colors flex items-center" title="การแจ้งเตือน">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute top-1 right-1 bg-rose-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </a>

                        <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2 text-slate-200 hover:text-white transition-colors">
                            <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-sm ring-2 ring-blue-400/30 overflow-hidden">
                                @if(Auth::user()->avatar && Storage::disk('public')->exists(Auth::user()->avatar))
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ Auth::user()->initials() }}
                                @endif
                            </div>
                            {{-- ชื่อยาวตัดด้วย ... และแสดงบทบาทใต้ชื่อแทนป้ายแยก เพื่อประหยัดพื้นที่ --}}
                            <div class="hidden sm:block text-left min-w-0">
                                <div class="text-sm font-semibold leading-tight truncate max-w-[10rem]" title="{{ Auth::user()->name }}">{{ Auth::user()->name }}</div>
                                <div class="text-xs mt-0.5 whitespace-nowrap {{ Auth::user()->isAdmin() ? 'text-amber-300' : 'text-slate-400' }}">
                                    {{ Auth::user()->isAdmin() ? '👑 ผู้ดูแลระบบ' : '🎓 '.(Auth::user()->student_id ?? 'นักศึกษา') }}
                                </div>
                            </div>
                        </a>

                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition-colors" title="ออกจากระบบ">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-300 hover:text-white px-3 py-2">
                            เข้าสู่ระบบ
                        </a>
                        <a href="{{ route('register') }}" class="text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-xl shadow-md transition-all">
                            สมัครสมาชิก
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    @auth
    {{-- เมนูจอเล็ก: เลื่อนซ้าย-ขวาได้แทนการขึ้นบรรทัดใหม่ --}}
    <nav class="lg:hidden flex gap-5 overflow-x-auto whitespace-nowrap px-4 py-3 bg-white border-b text-sm text-blue-600" aria-label="เมนูกิจกรรม">
        <a href="{{ route('activities.index') }}">กิจกรรม</a>
        <a href="{{ route('activities.create') }}">สร้างโพสต์</a>
        <a href="{{ route('my-activities.index') }}">นัดของฉัน</a>
        <a href="{{ route('attendance.index') }}">เช็กชื่อ</a>
        @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.users.index') }}" class="text-indigo-600">จัดการผู้ใช้งาน</a>
            <a href="{{ route('admin.categories.index') }}" class="text-indigo-600">จัดการหมวดหมู่</a>
            <a href="{{ route('admin.reports.index') }}" class="text-indigo-600">ตรวจรายงาน</a>
        @endif
    </nav>
    @endauth

    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(session('success'))
            <div id="flash-success" class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start justify-between gap-3 shadow-sm transition-opacity duration-500">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="text-sm font-medium">{{ session('success') }}</div>
                </div>
                <button onclick="document.getElementById('flash-success').style.display='none'" class="text-emerald-600 hover:text-emerald-800 text-sm font-bold">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div id="flash-error" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start justify-between gap-3 shadow-sm transition-opacity duration-500">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-rose-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="text-sm font-medium">{{ session('error') }}</div>
                </div>
                <button onclick="document.getElementById('flash-error').style.display='none'" class="text-rose-600 hover:text-rose-800 text-sm font-bold">✕</button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-white border-t border-slate-200 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500">
            &copy; 2026 UniMate - ระบบจับกลุ่มหาเพื่อนร่วมทำกิจกรรมในมหาวิทยาลัย
        </div>
    </footer>

    <script>
        // ตั้งเวลาให้กล่องข้อความแจ้งเตือนหายไปเองอัตโนมัติหลัง 4 วินาที
        setTimeout(() => {
            const successBox = document.getElementById('flash-success');
            if (successBox) successBox.style.display = 'none';
            const errorBox = document.getElementById('flash-error');
            if (errorBox) errorBox.style.display = 'none';
        }, 4000);

        // ปิดเมนู dropdown ของ Admin เมื่อคลิกที่อื่น
        document.addEventListener('click', (e) => {
            document.querySelectorAll('details[data-nav-dropdown][open]').forEach((d) => {
                if (!d.contains(e.target)) d.removeAttribute('open');
            });
        });
    </script>
    <style>details[data-nav-dropdown] > summary::-webkit-details-marker { display: none; }</style>

    @yield('scripts')
</body>
</html>