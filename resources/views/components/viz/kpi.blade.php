{{--
    การ์ดตัวเลขของหน้าภาพรวมข้อมูล ตัวเลขใหญ่ใช้ตัวเลขแบบปกติ (ไม่ใช้ tabular-nums / .stat-value)
    ใช้: <x-viz.kpi label="กิจกรรมที่จัด" value="104" sub="ยกเลิก 9 (8%)" />
--}}
@props(['label', 'value', 'sub' => null])
<div {{ $attributes->class(['card flex min-w-0 flex-col p-4 sm:p-5']) }}>
    <p class="text-sm text-ink-muted">{{ $label }}</p>
    <p class="mt-1 text-[1.75rem] font-medium leading-tight tracking-tight text-ink sm:text-[2rem]">{{ $value }}</p>
    @if($sub)
        <p class="mt-1 text-xs leading-relaxed text-ink-muted">{{ $sub }}</p>
    @endif
</div>
