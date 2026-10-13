{{-- ปุ่มเปิดตารางเทียบเท่าของกราฟ (ค่าทุกค่าในกราฟและ tooltip ต้องอยู่ในตารางนี้ด้วย) ใช้: <x-viz.table-toggle> <table>...</table> </x-viz.table-toggle> --}}
@props(['label' => 'ดูข้อมูลเป็นตาราง'])
<details {{ $attributes->class(['group mt-4 border-t border-line pt-3']) }}>
    <summary class="inline-flex cursor-pointer select-none items-center gap-1.5 rounded-full text-sm font-medium text-ink-muted transition hover:text-ink">
        <x-ui.icon name="chevron-right" class="h-4 w-4 transition group-open:rotate-90" />
        {{ $label }}
    </summary>
    <div class="mt-3 max-h-96 overflow-auto rounded-tile border border-line">
        {{ $slot }}
    </div>
</details>
