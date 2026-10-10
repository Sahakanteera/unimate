@php
    $value = old($field, ($activity->{$field} ?? $default)->copy()->timezone('Asia/Bangkok')->format('Y-m-d\TH:i'));
    $parts = explode('T', is_string($value) ? $value : '');
    $dateField = $field.'_date';
    $timeField = $field.'_time';
@endphp
<fieldset>
    <legend class="field-label">{{ $label }}</legend>
    <div class="grid grid-cols-[minmax(0,1fr)_100px] gap-2">
        <div>
            <label for="{{ $dateField }}" class="sr-only">วันที่{{ $label === 'เวลาเริ่ม' ? 'เริ่ม' : 'สิ้นสุด' }}</label>
            <input id="{{ $dateField }}" required type="date" name="{{ $dateField }}" value="{{ old($dateField, $parts[0] ?? '') }}" class="field {{ $invalid($dateField) }}" {{ $aria($dateField) }}>
        </div>
        <div>
            <label for="{{ $timeField }}" class="sr-only">{{ $label }} (24 ชั่วโมง)</label>
            <input id="{{ $timeField }}" required type="text" inputmode="numeric" name="{{ $timeField }}" maxlength="5" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" value="{{ old($timeField, $parts[1] ?? '') }}" placeholder="14:30" title="เวลาแบบ 24 ชั่วโมง เช่น 14:30" class="field tabular-nums {{ $invalid($timeField) }}" {{ $aria($timeField) }}>
        </div>
    </div>
    @error($field)<p id="{{ $field }}-error" class="field-error">{{ $message }}</p>@enderror
    @error($dateField)<p id="{{ $dateField }}-error" class="field-error">{{ $message }}</p>@enderror
    @error($timeField)<p id="{{ $timeField }}-error" class="field-error">{{ $message }}</p>@enderror
</fieldset>
