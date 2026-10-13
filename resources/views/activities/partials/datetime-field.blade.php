@php
    $value = old($field, ($activity->{$field} ?? $default)->copy()->timezone('Asia/Bangkok')->format('Y-m-d\TH:i'));
    $parts = explode('T', is_string($value) ? $value : '');
    $dateField = $field.'_date';
    $timeField = $field.'_time';
    $dateLabel = $field === 'starts_at' ? 'วันที่เริ่มกิจกรรม' : 'วันที่สิ้นสุดกิจกรรม';
    $timeLabel = $field === 'starts_at' ? 'ตั้งแต่เวลา' : 'ถึงเวลา';
    $selectedTime = old($timeField, $parts[1] ?? '');
@endphp
<div class="min-w-0 space-y-3">
    <div>
        <label for="{{ $dateField }}" class="field-label">{{ $dateLabel }}</label>
        <x-ui.date-input :id="$dateField" :name="$dateField" :value="old($dateField, $parts[0] ?? '')" :required="true" :invalid="$errors->has($dateField)" />
    </div>
    <div>
        <label for="{{ $timeField }}" class="field-label">{{ $timeLabel }}</label>
        <x-ui.time-input :id="$timeField" :name="$timeField" :value="$selectedTime" :label="$timeLabel" :required="true" :invalid="$errors->has($timeField)" />
    </div>
    @error($field)<p id="{{ $field }}-error" class="field-error">{{ $message }}</p>@enderror
    @error($dateField)<p id="{{ $dateField }}-error" class="field-error">{{ $message }}</p>@enderror
    @error($timeField)<p id="{{ $timeField }}-error" class="field-error">{{ $message }}</p>@enderror
</div>
