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
                    <nav class="hidden md:flex space-x-1 ml-6 pl-6 border-l border-slate-800">
                        <a href="#" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition-colors">
                            กิจกรรม
                        </a>
                        <a href="#" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition-colors">
                            สร้างโพสต์
                        </a>
                        <a href="#" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition-colors">
                            นัดของฉัน
                        </a>
                        
                        @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.users.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 hover:bg-indigo-600 hover:text-white transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            จัดการผู้ใช้งาน (Admin)
                        </a>
                        @endif
                    </nav>
                    @endauth
                </div>

                <div class="flex items-center space-x-4">
                    @auth
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ Auth::user()->isAdmin() ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-blue-500/20 text-blue-300 border border-blue-500/40' }}">
                            {{ Auth::user()->isAdmin() ? '👑 ผู้ดูแลระบบ' : '🎓 นักศึกษา' }}
                        </span>

                        <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2 text-slate-200 hover:text-white transition-colors">
                            <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-sm ring-2 ring-blue-400/30 overflow-hidden">
                                @if(Auth::user()->avatar && Storage::disk('public')->exists(Auth::user()->avatar))
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ Auth::user()->initials() }}
                                @endif
                            </div>
                            <div class="hidden sm:block text-left">
                                <div class="text-sm font-semibold leading-none">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-slate-400 mt-1">{{ Auth::user()->student_id ?? Auth::user()->email }}</div>
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

    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3 shadow-sm">
                <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="text-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-3 shadow-sm">
                <svg class="w-5 h-5 text-rose-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="text-sm font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-white border-t border-slate-200 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500">
            &copy; 2026 UniMate - ระบบจับกลุ่มหาเพื่อนร่วมทำกิจกรรมในมหาวิทยาลัย
        </div>
    </footer>

    @yield('scripts')
</body>
</html>
