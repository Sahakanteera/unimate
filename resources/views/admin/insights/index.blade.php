@extends('layouts.app')
@section('title', 'ภาพรวมข้อมูล (Admin) | UniMate')

@section('styles')
{{-- สีของกราฟตรวจด้วย validate_palette.js แล้ว (ดู docs/admin-insights-plan-th.md) อ้างผ่านชื่อ role เท่านั้น --}}
<style>
    :root {
        --viz-series: #2a78d6;
        --viz-heat-0: #f5f5f3;
        --viz-heat-1: #86b6ef;
        --viz-heat-2: #5598e7;
        --viz-heat-3: #2a78d6;
        --viz-heat-4: #1c5cab;
        --viz-heat-5: #0d366b;
    }
    .viz-bar { display: block; width: calc(100% - 2px); max-width: 24px; background: var(--viz-series); border-radius: 4px 4px 0 0; transition: filter 120ms ease-out; }
    .viz-col { cursor: default; outline: none; }
    .viz-col:hover .viz-bar, .viz-col:focus-visible .viz-bar { filter: brightness(1.18); }
    .viz-col:focus-visible { box-shadow: inset 0 -2px 0 0 #171717; }
    .viz-cell { display: block; height: 1.625rem; border-radius: 4px; background: var(--viz-heat-0); }
    @media (min-width: 640px) { .viz-cell { height: 1.875rem; } }
    .viz-cell:hover { outline: 2px solid #171717; outline-offset: 1px; position: relative; z-index: 1; }
    .viz-swatch { display: inline-block; width: 0.875rem; height: 0.875rem; border-radius: 3px; background: var(--viz-heat-0); }
    .viz-cell[data-level="1"], .viz-swatch[data-level="1"] { background: var(--viz-heat-1); }
    .viz-cell[data-level="2"], .viz-swatch[data-level="2"] { background: var(--viz-heat-2); }
    .viz-cell[data-level="3"], .viz-swatch[data-level="3"] { background: var(--viz-heat-3); }
    .viz-cell[data-level="4"], .viz-swatch[data-level="4"] { background: var(--viz-heat-4); }
    .viz-cell[data-level="5"], .viz-swatch[data-level="5"] { background: var(--viz-heat-5); }
    .viz-inline { display: block; height: 0.5rem; border-radius: 0 4px 4px 0; background: var(--viz-series); }
    .viz-table { width: 100%; font-size: 0.8125rem; }
    .viz-table th { background: #f5f5f3; padding: 0.5rem 0.75rem; text-align: left; font-weight: 500; color: #50555e; white-space: nowrap; position: sticky; top: 0; }
    .viz-table td { padding: 0.5rem 0.75rem; border-top: 1px solid #e4e8ef; color: #2b313b; font-variant-numeric: tabular-nums; }
    #viz-tip { position: fixed; left: 0; top: 0; z-index: 60; pointer-events: none; max-width: 16rem; }
</style>
@endsection

@section('content')
@php
    $trend = $data['trend'];
    $heat = $data['heatmap'];
    $cats = $data['categories'];
    $fmtPct = fn ($v) => $v === null ? '–' : \App\Support\AdminInsights::pct($v);
    $empty = fn (string $text) => '<div class="mt-4 flex flex-col items-center rounded-tile bg-canvas px-6 py-10 text-center"><p class="text-sm text-ink-muted">'.e($text).'</p></div>';
@endphp
<div class="space-y-6">
    @include('admin.partials.header', [
        'title' => 'ภาพรวมข้อมูล',
        'description' => 'สรุปการใช้งานระบบในช่วงที่เลือก: จำนวนกิจกรรม แนวโน้ม วันและเวลาที่นิยม และหมวดหมู่ที่ได้ผลดี ดาวน์โหลดเป็นไฟล์ CSV ไปใช้ต่อได้',
    ])

    {{-- ตัวกรองช่วงเวลาและปุ่มดาวน์โหลด: มีผลกับทุกส่วนด้านล่าง --}}
    <section aria-labelledby="period-heading" class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 id="period-heading" class="section-title">ช่วงเวลา</h2>
                <p class="mt-0.5 text-sm text-ink-muted">{{ $data['periodLabel'] }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <nav class="flex flex-wrap gap-2" aria-label="เลือกช่วงเวลา">
                    @foreach($data['ranges'] as $key => $label)
                        <a href="{{ route('admin.insights.index', ['range' => $key]) }}" @if($data['range'] === $key) aria-current="page" @endif
                           class="chip h-9 px-4 text-sm transition {{ $data['range'] === $key ? 'chip-dark' : 'chip-outline hover:border-ink-faint' }}">{{ $label }}</a>
                    @endforeach
                </nav>
                <a href="{{ route('admin.insights.export', ['range' => $data['range']]) }}" class="btn btn-secondary btn-sm" download>
                    <x-ui.icon name="arrow-down-tray" class="h-4 w-4" />
                    ดาวน์โหลด CSV
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            @foreach($data['kpis']['cards'] as $i => $card)
                <x-viz.kpi class="reveal" style="--i: {{ $i }}" :label="$card['label']" :value="$card['value']" :sub="$card['sub']" />
            @endforeach
        </div>
    </section>

    {{-- แนวโน้ม --}}
    <section class="card p-5 sm:p-6" aria-labelledby="trend-heading">
        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
            <h2 id="trend-heading" class="section-title">จำนวนกิจกรรมที่จัด</h2>
            <p class="text-xs text-ink-muted">{{ $trend['unitLabel'] }} · ไม่รวมกิจกรรมที่ยกเลิก</p>
        </div>
        @if($trend['total'] > 0)
            <p class="mt-1 text-sm text-ink-muted"><span class="font-medium text-ink-soft">ข้อสังเกต:</span> {{ $trend['insight'] }}</p>
            @php $bars = array_map(fn ($b) => $b + ['tip' => number_format($b['value']).' กิจกรรม'], $trend['bars']); @endphp
            <x-viz.column-chart class="mt-4" :bars="$bars" :max="$trend['max']" />
            <x-viz.table-toggle>
                <table class="viz-table">
                    <thead><tr><th>ช่วง</th><th class="text-right">กิจกรรม</th></tr></thead>
                    <tbody>
                        @foreach($trend['bars'] as $bar)
                            <tr><td>{{ $bar['title'] }}</td><td class="text-right">{{ number_format($bar['value']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </x-viz.table-toggle>
        @else
            {!! $empty('ยังไม่มีกิจกรรมในช่วงนี้') !!}
        @endif
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Heatmap วัน × เวลา --}}
        <section class="card min-w-0 p-5 sm:p-6" aria-labelledby="heatmap-heading">
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                <h2 id="heatmap-heading" class="section-title">กิจกรรมเริ่มวันไหน เวลาไหน</h2>
                <p class="text-xs text-ink-muted">นับตามเวลาเริ่มกิจกรรม · รวม {{ number_format($heat['total']) }}</p>
            </div>
            @if($heat['total'] > 0)
                <p class="mt-1 text-sm text-ink-muted"><span class="font-medium text-ink-soft">ข้อสังเกต:</span> {{ $heat['insight'] }}</p>
                <x-viz.heatmap class="mt-4" :data="$heat" />
                <x-viz.table-toggle>
                    <table class="viz-table min-w-[44rem]">
                        <thead><tr><th>วัน</th>@foreach($heat['hours'] as $h)<th class="text-right">{{ $h }}</th>@endforeach<th class="text-right">รวม</th></tr></thead>
                        <tbody>
                            @foreach($heat['rows'] as $row)
                                <tr><td>{{ $row['name'] }}</td>@foreach($row['cells'] as $cell)<td class="text-right {{ $cell['value'] ? '' : 'text-ink-faint' }}">{{ $cell['value'] }}</td>@endforeach<td class="text-right font-medium">{{ $row['total'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-viz.table-toggle>
            @else
                {!! $empty('ยังไม่มีกิจกรรมในช่วงนี้') !!}
            @endif
        </section>

        {{-- แยกตามหมวดหมู่ --}}
        <section class="card min-w-0 p-5 sm:p-6" aria-labelledby="category-heading">
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                <h2 id="category-heading" class="section-title">แยกตามหมวดหมู่</h2>
                <p class="text-xs text-ink-muted">เรียงจากจำนวนกิจกรรมมากไปน้อย</p>
            </div>
            @if($cats['max'] > 0)
                <p class="mt-1 text-sm text-ink-muted"><span class="font-medium text-ink-soft">ข้อสังเกต:</span> {{ $cats['insight'] }}</p>
            @endif
            @if($cats['rows'] === [])
                {!! $empty('ยังไม่มีหมวดหมู่') !!}
            @else
                <ul class="mt-3 divide-y divide-line">
                    @foreach($cats['rows'] as $row)
                        <li class="py-3">
                            <div class="flex items-baseline justify-between gap-3">
                                <span @class(['break-anywhere min-w-0 text-sm', 'font-medium text-ink' => $row['activities'] > 0, 'text-ink-faint' => $row['activities'] === 0])>{{ $row['name'] }}</span>
                                <span @class(['shrink-0 text-sm tabular-nums', 'text-ink' => $row['activities'] > 0, 'text-ink-faint' => $row['activities'] === 0])>{{ number_format($row['activities']) }} กิจกรรม</span>
                            </div>
                            @if($row['activities'] > 0)
                                <span class="mt-1.5 block"><span class="viz-inline" style="width: {{ $row['activities'] / $cats['max'] * 100 }}%"></span></span>
                                <p class="mt-1.5 text-xs text-ink-muted">
                                    เติมที่นั่ง {{ $fmtPct($row['fillRate']) }} · มาตามนัด {{ $fmtPct($row['attendanceRate']) }} ·
                                    {{ $row['rating'] !== null ? '★ '.number_format($row['rating'], 1).' ('.$row['reviews'].' รีวิว)' : 'ยังไม่มีรีวิว' }}
                                </p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <p class="text-xs leading-relaxed text-ink-muted">
        วิธีนับ: นับกิจกรรมตามวันเริ่มกิจกรรมที่อยู่ในช่วงที่เลือก (รวมกิจกรรมที่ถูกซ่อน) ·
        เติมที่นั่ง = ผู้ได้รับอนุมัติ ÷ จำนวนที่นั่ง · มาตามนัด = มา ÷ (มา + ขาด) · เวลาทั้งหมดเป็นเวลาประเทศไทย
    </p>
</div>

{{-- tooltip ตัวเดียวทั้งหน้า ใส่ข้อความด้วย textContent เท่านั้น --}}
<div id="viz-tip" hidden class="rounded-xl bg-night px-3 py-2 text-white shadow-float">
    <p data-tip-value-el class="text-sm font-medium tabular-nums"></p>
    <p data-tip-title-el class="mt-0.5 text-xs text-white/75"></p>
</div>
@endsection

@section('scripts')
<script>
    (() => {
        const tip = document.getElementById('viz-tip');
        if (!tip) return;
        const valueEl = tip.querySelector('[data-tip-value-el]');
        const titleEl = tip.querySelector('[data-tip-title-el]');
        let current = null;

        const place = (x, y) => {
            const w = tip.offsetWidth, h = tip.offsetHeight;
            let left = x + 14, top = y + 14;
            if (left + w > window.innerWidth - 8) left = Math.max(8, x - w - 14);
            if (top + h > window.innerHeight - 8) top = Math.max(8, y - h - 14);
            tip.style.transform = `translate(${left}px, ${top}px)`;
        };
        const show = (el, x, y) => {
            current = el;
            valueEl.textContent = el.dataset.tipValue || '';
            titleEl.textContent = el.dataset.tipTitle || '';
            tip.hidden = false;
            place(x, y);
        };
        const hide = () => { current = null; tip.hidden = true; };

        document.addEventListener('pointerover', (e) => {
            const el = e.target.closest('[data-tip-title]');
            if (el) show(el, e.clientX, e.clientY); else if (current) hide();
        });
        document.addEventListener('pointermove', (e) => { if (current) place(e.clientX, e.clientY); });
        document.addEventListener('focusin', (e) => {
            const el = e.target.closest('[data-tip-title]');
            if (!el) return;
            const r = el.getBoundingClientRect();
            show(el, r.left + r.width / 2, r.top);
        });
        document.addEventListener('focusout', hide);
        window.addEventListener('scroll', () => { if (current && document.activeElement !== current) hide(); }, { passive: true });
    })();
</script>
@endsection
