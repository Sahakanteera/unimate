<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f5f5f3">
    <title>@yield('title', 'UniMate - ระบบจับกลุ่มหาเพื่อนร่วมทำกิจกรรมในมหาวิทยาลัย')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="description" content="ค้นหากิจกรรมที่สนใจ ชวนเพื่อนร่วมเล่นกีฬา ติวหนังสือ ท่องเที่ยว และทำจิตอาสาในมหาวิทยาลัยกับ UniMate">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="UniMate">
    <meta property="og:title" content="UniMate · หาเพื่อนร่วมกิจกรรมในมหาวิทยาลัย">
    <meta property="og:description" content="เริ่มจากกิจกรรมที่ชอบ แล้วไปเจอเพื่อนใหม่">
    <meta property="og:image" content="{{ asset('images/web/og/unimate-og.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="ภาพกระดาษนักศึกษาร่วมกิจกรรมหลากหลายหมวดหมู่">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ asset('images/web/og/unimate-og.png') }}">

    {{-- ฟอนต์ สี และคลาสส่วนกลาง (ปุ่ม การ์ด ฟอร์ม แท็บ) อยู่ใน partials/theme --}}
    @include('partials.theme')

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
<body class="flex min-h-full flex-col bg-canvas text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-night focus:px-4 focus:py-2 focus:text-white">ข้ามไปที่เนื้อหา</a>

    @include('partials.nav')
    @include('partials.flash')

    <main id="main" class="mx-auto w-full max-w-7xl flex-1 px-4 pb-12 pt-6 sm:px-6 sm:pt-8 lg:px-8">
        @yield('content')
    </main>

    @include('partials.footer')

    @auth
        @include('partials.bottom-nav')
    @endauth

    <script>
        // ข้อความสำเร็จจางหายเองหลัง 4 วินาที (ข้อความผิดพลาดค้างไว้ให้อ่านจนกดปิด)
        setTimeout(() => {
            document.querySelectorAll('[data-flash][data-autohide]').forEach((box) => {
                box.style.opacity = '0';
                setTimeout(() => box.remove(), 500);
            });
        }, 4000);

        // ปิดเมนู dropdown (Admin / บัญชี) เมื่อคลิกที่อื่นหรือกด Esc และเปิดได้ครั้งละอันเดียว
        const navDropdowns = () => document.querySelectorAll('details[data-nav-dropdown][open]');
        document.addEventListener('click', (e) => {
            navDropdowns().forEach((d) => { if (!d.contains(e.target)) d.removeAttribute('open'); });
        });
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            navDropdowns().forEach((d) => { d.removeAttribute('open'); d.querySelector('summary')?.focus(); });
        });
    </script>

    @yield('scripts')
</body>
</html>
