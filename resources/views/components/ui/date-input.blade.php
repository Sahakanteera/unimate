@props(['id', 'name', 'value' => '', 'required' => false, 'invalid' => false])
<div class="relative" data-date-input>
    <input id="{{ $id }}" name="{{ $name }}" type="text" data-date-picker placeholder="วัน/เดือน/ปี" maxlength="10" inputmode="numeric" value="{{ App\Support\ActivityDate::display($value) }}" @required($required) {{ $attributes->class(['field pr-12 tabular-nums', 'field-invalid' => $invalid]) }}>
    <button type="button" data-open-calendar aria-label="เปิดปฏิทิน" aria-controls="{{ $id }}" class="absolute right-1 top-1 grid h-10 w-10 place-items-center rounded-full text-ink-muted hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-brand-600">
        <x-ui.icon name="calendar" class="h-4 w-4" />
    </button>
</div>
