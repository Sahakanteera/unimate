{{--
    กราฟแท่งแนวตั้ง 1 ชุดข้อมูล (สี --viz-series) แท่งหนาไม่เกิน 24px ปลายมน 4px เว้นระยะ 2px
    ป้ายตัวเลขเฉพาะค่าสูงสุดและแท่งล่าสุด ค่าอื่นดูจากแกน y, tooltip และตารางเทียบเท่า
    bars: list ของ ['label' => ป้ายแกน x, 'title' => ชื่อเต็มใน tooltip, 'value' => int, 'tip' => ข้อความค่าใน tooltip]
--}}
@props(['bars', 'max', 'unit' => ''])
@php
    $count = count($bars);
    // ขั้นแกน y แบบตัวเลขกลม 1/2/5 × 10^k ประมาณ 3-4 ระดับ
    $raw = max(1, $max) / 3;
    $mag = 10 ** floor(log10($raw));
    $step = 1;
    foreach ([1, 2, 5, 10] as $m) {
        if ($m * $mag >= $raw) { $step = max(1, (int) ceil($m * $mag)); break; }
    }
    $top = (int) (ceil(max(1, $max) / $step) * $step);
    $ticks = range(0, $top, $step);

    // ป้ายแกน x แบบเว้นระยะ: จอใหญ่ไม่เกิน ~10 ป้าย มือถือไม่เกิน ~5 ป้าย
    $stepLg = max(1, (int) ceil($count / 10));
    $stepSm = $stepLg * max(1, (int) ceil(($count / $stepLg) / 5));

    $values = array_column($bars, 'value');
    $peak = $max > 0 ? array_search($max, $values, true) : null;
    $last = $count > 0 && $bars[$count - 1]['value'] > 0 ? $count - 1 : null;
@endphp
<div {{ $attributes->class(['flex gap-2']) }}>
    {{-- แกน y --}}
    <div class="relative mt-5 h-44 w-7 shrink-0 text-right text-[11px] tabular-nums text-ink-muted" aria-hidden="true">
        @foreach($ticks as $tick)
            <span class="absolute right-0 translate-y-1/2 leading-none" style="bottom: {{ $tick / $top * 100 }}%">{{ number_format($tick) }}</span>
        @endforeach
    </div>
    <div class="min-w-0 flex-1">
        <div class="relative mt-5 h-44">
            @foreach($ticks as $tick)
                <span class="absolute inset-x-0 h-px bg-line" style="bottom: {{ $tick / $top * 100 }}%" aria-hidden="true"></span>
            @endforeach
            <div class="absolute inset-0 flex items-end">
                @foreach($bars as $i => $bar)
                    @php $h = $bar['value'] / $top * 100; @endphp
                    <div class="viz-col relative flex h-full min-w-0 flex-1 items-end justify-center" tabindex="0"
                         data-tip-title="{{ $bar['title'] }}" data-tip-value="{{ $bar['tip'] }}" aria-label="{{ $bar['title'] }}: {{ $bar['tip'] }}">
                        @if($bar['value'] > 0)
                            <span class="viz-bar" style="height: {{ $h }}%"></span>
                        @endif
                        @if($i === $peak || $i === $last)
                            <span class="pointer-events-none absolute left-1/2 -translate-x-1/2 whitespace-nowrap text-[11px] font-medium leading-none tabular-nums text-ink-soft" style="bottom: calc({{ $h }}% + 5px)">{{ number_format($bar['value']) }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        {{-- แกน x --}}
        <div class="relative flex h-6 text-[11px] text-ink-muted" aria-hidden="true">
            @foreach($bars as $i => $bar)
                <div class="relative min-w-0 flex-1">
                    @if($i % $stepLg === 0)
                        <span @class([
                            'absolute top-1.5 whitespace-nowrap leading-none tabular-nums',
                            'hidden sm:block' => $i % $stepSm !== 0,
                            'left-0' => $i === 0,
                            'right-0' => $i !== 0 && $i === $count - 1,
                            'left-1/2 -translate-x-1/2' => $i !== 0 && $i !== $count - 1,
                        ])>{{ $bar['label'] }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
