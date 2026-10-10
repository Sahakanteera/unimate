{{--
    Heatmap วัน (จ.-อา.) × ช่วงเวลา 2 ชั่วโมง สีไล่ฟ้า 5 ระดับ (--viz-heat-1..5) ช่องที่เป็น 0 ใช้สีพื้น (--viz-heat-0)
    ไม่ใส่ตัวเลขในช่อง ค่าดูได้จาก tooltip และตารางเทียบเท่า ใช้: <x-viz.heatmap :data="$data['activityHeatmap']" />
--}}
@props(['data'])
<div {{ $attributes }}>
    <div role="img" aria-label="{{ $data['summary'] }}">
        <div class="viz-heat grid gap-[2px]" style="grid-template-columns: 1.75rem repeat(12, minmax(0, 1fr));">
            <span></span>
            @foreach($data['hours'] as $hour)
                <span class="pb-1 text-center text-[10px] leading-none tabular-nums text-ink-muted sm:text-[11px]">{{ $hour }}</span>
            @endforeach
            @foreach($data['rows'] as $row)
                <span class="self-center text-xs text-ink-muted">{{ $row['label'] }}</span>
                @foreach($row['cells'] as $cell)
                    <span class="viz-cell" data-level="{{ $cell['level'] }}"
                          data-tip-title="{{ $cell['title'] }}" data-tip-value="{{ number_format($cell['value']) }} {{ $data['noun'] }}"></span>
                @endforeach
            @endforeach
        </div>
        <p class="mt-1.5 text-right text-[11px] text-ink-muted">เวลา (นาฬิกา)</p>
    </div>
    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs text-ink-muted">
        <span>จำนวน{{ $data['noun'] }}ต่อช่อง</span>
        <span class="inline-flex items-center gap-1.5"><span class="viz-swatch" data-level="0"></span><span class="tabular-nums">0</span></span>
        @foreach($data['legend'] as $item)
            <span class="inline-flex items-center gap-1.5"><span class="viz-swatch" data-level="{{ $item['level'] }}"></span><span class="tabular-nums">{{ $item['label'] }}</span></span>
        @endforeach
    </div>
</div>
