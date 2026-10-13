(() => {
    const parseDate = (value) => {
        const parts = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(value);
        if (!parts) return undefined;
        const day = Number(parts[1]), month = Number(parts[2]) - 1, year = Number(parts[3]);
        const date = new Date(year, month, day);
        return date.getFullYear() === year && date.getMonth() === month && date.getDate() === day ? date : undefined;
    };
    document.querySelectorAll('[data-date-input]').forEach((wrapper) => {
        const input = wrapper.querySelector('[data-date-picker]');
        let calendar;
        if (window.flatpickr) {
            calendar = flatpickr(input, {
                locale: flatpickr.l10ns.th || 'default',
                dateFormat: 'd/m/Y',
                ariaDateFormat: 'j F Y',
                monthSelectorType: 'static',
                disableMobile: true,
                allowInput: true,
                parseDate,
            });
        }
        wrapper.querySelector('[data-open-calendar]').addEventListener('click', () => {
            if (calendar) calendar.open();
            else input.focus();
        });
    });
})();
