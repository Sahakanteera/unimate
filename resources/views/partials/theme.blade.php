{{--
    ธีมกลางของ UniMate (ใช้กับ layouts.app ทุกหน้า)
    ประยุกต์จากภาษาออกแบบของ fastwork.com: พื้นออฟไวต์อุ่น การ์ดขาวมุมโค้งใหญ่ ปุ่มแคปซูลสีเข้ม
    ฟอนต์ Google Sans (มีชุดอักษรไทย) แท็บขีดเส้นใต้ และสีพาสเทลแยกหมวดหมู่
    รายละเอียดค่าที่วัดจากต้นแบบและวิธีใช้คลาส ดู docs/ui-design-th.md
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<script src="https://cdn.tailwindcss.com/3.4.17"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['"Google Sans"', '"Noto Sans Thai"', 'system-ui', '-apple-system', '"Segoe UI"', 'sans-serif'],
                    note: ['"Playpen Sans Thai"', '"Google Sans"', 'sans-serif'],
                },
                colors: {
                    canvas: '#f5f5f3',
                    ink: { DEFAULT: '#171717', soft: '#2b313b', muted: '#50555e', faint: '#737373' },
                    line: { DEFAULT: '#e4e8ef', strong: '#d2d4d9' },
                    night: { DEFAULT: '#131922', soft: '#222a36' },
                    brand: { 50: '#eef5ff', 100: '#dceaff', 200: '#b9d6ff', 500: '#2483ff', 600: '#0569ff', 700: '#0052cc' },
                    tint: { mint: '#b3efbd', aqua: '#b3f4ef', lilac: '#d3bdff', coral: '#ffafa3', butter: '#ffe299', peach: '#fdd3a8' },
                    ok: { DEFAULT: '#1d7a0c', soft: '#e4f6e0' },
                    warn: { DEFAULT: '#7a5300', soft: '#fff3cd' },
                    bad: { DEFAULT: '#c4231b', soft: '#ffebe8' },
                },
                borderRadius: { tile: '14px', card: '24px' },
                boxShadow: {
                    soft: '0 1px 1px rgba(0, 0, 0, .04), 0 2px 4px rgba(0, 0, 0, .06)',
                    lift: '0 2.5px 5px rgba(15, 23, 42, .06), 0 12.6px 18px rgba(15, 23, 42, .10)',
                    float: '0 13px 35px rgba(15, 23, 42, .10), 0 3px 10px rgba(15, 23, 42, .06)',
                },
                transitionTimingFunction: { 'out-expo': 'cubic-bezier(0.16, 1, 0.3, 1)' },
                keyframes: {
                    'drop-in': { '0%': { opacity: '0', transform: 'translateY(-8px) scale(.98)' }, '100%': { opacity: '1', transform: 'none' } },
                },
                animation: {
                    'drop-in': 'drop-in 280ms cubic-bezier(0.16, 1, 0.3, 1) both',
                },
            },
        },
    }
</script>

<style type="text/tailwindcss">
    @layer base {
        html { -webkit-tap-highlight-color: transparent; scroll-padding-top: 6rem; }
        body { @apply bg-canvas font-sans text-ink antialiased; }
        :focus-visible { @apply outline-none ring-4 ring-brand-600/25; }
        ::selection { @apply bg-brand-100; }
        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
    }

    @layer components {
        /* ปุ่มแคปซูลแบบ fastwork: ปุ่มหลักสีเข้ม ปุ่มรองขอบบาง */
        .btn { @apply inline-flex h-11 items-center justify-center gap-2 whitespace-nowrap rounded-full px-5 text-[15px] font-medium leading-none transition duration-150 ease-out-expo select-none disabled:pointer-events-none disabled:opacity-50; }
        .btn-primary { @apply bg-night text-white hover:bg-black active:scale-[.98]; }
        .btn-secondary { @apply border border-line-strong bg-white text-ink hover:border-ink-faint hover:bg-canvas; }
        .btn-ghost { @apply text-ink-muted hover:bg-black/5 hover:text-ink; }
        .btn-danger { @apply border border-bad/25 bg-white text-bad hover:border-bad/40 hover:bg-bad-soft; }
        .btn-success { @apply bg-ok text-white hover:brightness-110; }
        .btn-sm { @apply h-9 px-4 text-sm; }
        .btn-lg { @apply h-12 px-6 text-base; }
        .btn-icon { @apply h-11 w-11 px-0; }

        .card { @apply rounded-card border border-line/80 bg-white shadow-soft; }
        .panel { @apply rounded-tile bg-canvas; }

        .chip { @apply inline-flex h-7 items-center gap-1.5 whitespace-nowrap rounded-full bg-canvas px-3 text-[13px] font-medium leading-none text-ink-soft; }
        .chip-ok { @apply bg-ok-soft text-ok; }
        .chip-warn { @apply bg-warn-soft text-warn; }
        .chip-bad { @apply bg-bad-soft text-bad; }
        .chip-info { @apply bg-brand-50 text-brand-700; }
        .chip-dark { @apply bg-night text-white; }
        .chip-outline { @apply border border-line bg-white; }

        .field-label { @apply mb-1.5 block text-sm font-medium text-ink-soft; }
        .field { @apply block h-12 w-full rounded-tile border border-line-strong bg-white px-4 text-[15px] text-ink transition duration-150 placeholder:text-ink-faint focus:border-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-600/15; }
        textarea.field { @apply h-auto py-3 leading-relaxed; }
        select.field { @apply appearance-none bg-no-repeat pr-10; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='%2350555e'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='m19.5 8.25-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E"); background-position: right .9rem center; background-size: 1rem; }
        .field-invalid { @apply border-bad focus:border-bad focus:ring-bad/15; }
        .field-hint { @apply mt-1.5 text-xs text-ink-muted; }
        .field-error { @apply mt-1.5 text-xs font-medium text-bad; }

        /* แท็บขีดเส้นใต้ 2px แบบแท็บโปรไฟล์ของ fastwork */
        .tabs { @apply flex gap-6 overflow-x-auto border-b border-line; scrollbar-width: none; }
        .tab { @apply relative inline-flex h-12 shrink-0 items-center gap-2 whitespace-nowrap text-[15px] text-ink-muted transition hover:text-ink; }
        .tab-active { @apply font-medium text-ink shadow-[inset_0_-2px_0_0_#171717]; }
        .tab-count { @apply inline-grid h-5 min-w-[1.25rem] place-items-center rounded-full bg-canvas px-1.5 text-xs font-medium text-ink-muted; }

        /* ช่องตัวเลขแบบช่องนับถอยหลังของ fastwork */
        .stat { @apply rounded-tile bg-canvas px-4 py-3.5; }
        .stat-value { @apply text-2xl font-medium leading-tight tracking-tight text-ink; }
        .stat-label { @apply mt-1 text-xs text-ink-muted; }

        .kicker { @apply inline-flex h-8 items-center gap-2 rounded-full border border-line bg-white/70 px-3.5 text-[13px] font-medium text-ink-muted; }
        .page-title { @apply text-[30px] font-medium leading-tight tracking-tight text-ink-soft sm:text-[40px]; }
        .section-title { @apply text-lg font-medium text-ink; }
        /* ไล่สีฟ้า→ชมพู→ส้มแบบ fastwork: บนพื้นสว่างใช้โทนเข้มขึ้นให้อ่านได้ (≥3:1), บนพื้นเข้มใช้โทนสดเดิม */
        .text-gradient { @apply bg-clip-text text-transparent; background-image: linear-gradient(90deg, #0569ff 0%, #d43bc9 55%, #e05a00 100%); }
        .text-gradient-bright { @apply bg-clip-text text-transparent; background-image: linear-gradient(90deg, #82bcff 0%, #ff66f4 50%, #fe7b02 100%); }
        .link { @apply font-medium text-brand-700 underline-offset-4 hover:underline; }
        .badge-count { @apply inline-grid h-5 min-w-[1.25rem] place-items-center rounded-full bg-bad px-1 text-[11px] font-semibold leading-none text-white; }

        /* ให้คะแนนรีวิว: ดาวเรียง 1-5 ใช้ radio เดิม (DOM เรียง 5→1 แล้วกลับด้านด้วย flex-row-reverse) */
        .star-rating { @apply inline-flex flex-row-reverse justify-end gap-1; }
        .star-rating label { @apply cursor-pointer text-[34px] leading-none text-line-strong transition duration-150; }
        .star-rating input:checked ~ label { @apply text-amber-400; }
        /* ขณะชี้เมาส์: แสดงตัวอย่างตามดาวที่ชี้อยู่ แทนค่าที่เลือกไว้ */
        .star-rating:hover label { color: #d2d4d9 !important; }
        .star-rating label:hover,
        .star-rating label:hover ~ label { color: #fbbf24 !important; }
        .star-rating input:focus-visible + label { @apply rounded-md ring-4 ring-brand-600/25; }

        .reveal { animation: um-fade-up 420ms cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: calc(var(--i, 0) * 45ms); }
        @media (prefers-reduced-motion: reduce) {
            .reveal, .animate-drop-in { animation: none; }
            .btn, .card { transition: none; }
        }
    }

    @keyframes um-fade-up {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: none; }
    }
</style>
