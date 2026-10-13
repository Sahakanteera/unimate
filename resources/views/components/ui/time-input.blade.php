@props(['id', 'name', 'value' => '', 'label', 'required' => false, 'invalid' => false])
@php $selectedTime = old($name, is_string($value) ? $value : ''); @endphp
<div data-time-input>
    <div class="relative">
        <input id="{{ $id }}" name="{{ $name }}" type="text" data-time-picker inputmode="numeric" maxlength="5" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" placeholder="เช่น 15:48" value="{{ is_string($selectedTime) ? $selectedTime : '' }}" @required($required) aria-label="{{ $label }}" aria-describedby="{{ $id }}-hint{{ $invalid ? ' '.$id.'-error' : '' }}" @if($invalid) aria-invalid="true" @endif class="field font-medium tabular-nums {{ $required ? 'pr-12' : 'pr-20' }} {{ $invalid ? 'field-invalid' : '' }}">
        <button type="button" data-open-time aria-label="เลือก{{ $label }}" aria-controls="{{ $id }}" class="absolute right-1 top-1 grid h-10 w-10 place-items-center rounded-full text-ink-muted transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-brand-600"><x-ui.icon name="clock" class="h-4 w-4" /></button>
        @unless($required)
            <button type="button" data-clear-time aria-label="ล้าง{{ $label }}" class="absolute right-11 top-1 grid h-10 w-8 place-items-center rounded-full text-ink-muted transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-brand-600 [&[hidden]]:hidden" @if(!$selectedTime) hidden @endif><x-ui.icon name="x" class="h-3.5 w-3.5" /></button>
        @endunless
    </div>
    <p id="{{ $id }}-hint" class="field-hint">{{ $required ? '24 ชั่วโมง · เลือกหรือพิมพ์ได้ทุกนาที' : 'ไม่ระบุเวลา = ทุกเวลา' }}</p>
</div>
