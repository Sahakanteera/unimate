const configureTimeScrolling = (instance) => {
    const hour = instance.calendarContainer.querySelector('.flatpickr-hour');
    const minute = instance.calendarContainer.querySelector('.flatpickr-minute');
    if (!hour || !minute) return;

    const normalize = (control, max, padded = false) => {
        const raw = control.value;
        if (raw === '' && !padded) return;
        const digits = raw.startsWith('-') ? '0' : raw.replace(/\D/g, '').slice(0, 2);
        const value = Math.min(max, Math.max(0, Number(digits) || 0));
        control.value = padded ? String(value).padStart(2, '0') : String(value);
    };
    const commit = () => {
        normalize(hour, 23, true);
        normalize(minute, 59, true);
        const date = new Date(instance.selectedDates[0]?.getTime() ?? Date.now());
        date.setHours(Number(hour.value), Number(minute.value), 0, 0);
        instance.setDate(date, true);
    };
    [[hour, 23, 'ชั่วโมง (00–23)'], [minute, 59, 'นาที (00–59)']].forEach(([control, max, label]) => {
        control.min = '0';
        control.max = String(max);
        control.step = '1';
        control.inputMode = 'numeric';
        control.setAttribute('aria-label', label);
        control.addEventListener('keydown', (event) => {
            if (!event.ctrlKey && !event.metaKey && ['e', 'E', '+', '-', '.'].includes(event.key)) event.preventDefault();
        }, true);
        // input[type=number] ไม่บังคับ maxlength จึงจำกัดจำนวนหลักและช่วงค่าทันที
        control.addEventListener('input', () => normalize(control, max), true);
        control.addEventListener('change', commit);
        control.addEventListener('blur', commit);

        let lastWheel = -Infinity;
        control.closest('.numInputWrapper').addEventListener('wheel', (event) => {
            if (!event.deltaY) return;
            event.preventDefault();
            event.stopPropagation();
            const now = performance.now();
            if (now - lastWheel < 60) return;
            lastWheel = now;
            normalize(control, max, true);
            const step = event.deltaY < 0 ? 1 : -1;
            control.value = String((Number(control.value) + step + max + 1) % (max + 1));
            commit();
        }, { passive: false });
    });
    const hint = document.createElement('div');
    hint.className = 'time-wheel-hint';
    hint.textContent = 'หมุนล้อเมาส์บนชั่วโมง / นาทีเพื่อเลือก';
    instance.calendarContainer.append(hint);
};

document.querySelectorAll('[data-time-input]').forEach((group) => {
    const input = group.querySelector('[data-time-picker]');
    const clearButton = group.querySelector('[data-clear-time]');
    const updateClear = () => {
        if (clearButton) clearButton.hidden = input.value === '';
    };
    let picker;
    if (window.flatpickr) {
        picker = flatpickr(input, {
            locale: flatpickr.l10ns.th || 'default',
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true,
            minuteIncrement: 1,
            hourIncrement: 1,
            disableMobile: true,
            allowInput: true,
            parseDate: (value) => {
                const match = /^(?:([01]\d|2[0-3])):?([0-5]\d)$/.exec(value);
                return match ? new Date(2000, 0, 1, Number(match[1]), Number(match[2])) : undefined;
            },
            onValueUpdate: () => input.dispatchEvent(new Event('input', { bubbles: true })),
            onChange: updateClear,
            onReady: (_dates, _value, instance) => configureTimeScrolling(instance),
        });
    }
    input.addEventListener('input', updateClear);
    group.querySelector('[data-open-time]').addEventListener('click', () => {
        if (picker) picker.open();
        else input.focus();
    });
    clearButton?.addEventListener('click', () => {
        if (picker) picker.clear();
        input.value = '';
        updateClear();
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.focus();
        picker?.close();
    });
    updateClear();
});

const durationNote = document.querySelector('[data-activity-duration]');
if (durationNote) {
    const form = durationNote.closest('form');
    const readTime = (field) => {
        const date = form.querySelector(`#${field}_date`).value;
        const timeInput = form.querySelector(`#${field}_time`);
        const time = /^([01]\d|2[0-3]):([0-5]\d)$/.exec(timeInput.value);
        const match = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(date);
        if (!match || !time) return null;
        const timestamp = Date.UTC(Number(match[3]), Number(match[2]) - 1, Number(match[1]), Number(time[1]), Number(time[2]));
        const checked = new Date(timestamp);
        if (checked.getUTCDate() !== Number(match[1]) || checked.getUTCMonth() !== Number(match[2]) - 1 || checked.getUTCFullYear() !== Number(match[3])) return null;
        return timestamp;
    };
    const updateDuration = () => {
        const start = readTime('starts_at'), end = readTime('ends_at');
        durationNote.hidden = start === null || end === null;
        if (durationNote.hidden) return;
        const minutes = (end - start) / 60000;
        durationNote.classList.toggle('text-bad', minutes <= 0);
        durationNote.textContent = minutes <= 0 ? 'เลือกเวลาสิ้นสุดให้หลังเวลาเริ่ม' : `ระยะเวลากิจกรรม ${Math.floor(minutes / 60) ? Math.floor(minutes / 60) + ' ชั่วโมง ' : ''}${minutes % 60 ? minutes % 60 + ' นาที' : ''}`.trim();
    };
    form.addEventListener('input', updateDuration);
    form.addEventListener('change', updateDuration);
    updateDuration();
}
