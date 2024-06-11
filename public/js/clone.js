/*
 * Init field cloning
 */
export function initClonability() {
    form.addEventListener('click', cloneHandler);
}

function cloneHandler(e) {
    if (e.target.matches('button.clone-field')) {

        const elem = e.target;
        const cloned = elem.parentNode.parentNode.cloneNode(true);
        const input = cloned.querySelector('input');

        cloned.id = cloned.id.replace(/(\d+$)/, function (match, number) {
            return (parseInt(number, 10) + 1);
        });

        const newId = input.id.replace(/(\d+$)/, function(match, number) {
            return (parseInt(number, 10) + 1);
        });
        input.value = '';
        input.setAttribute('value', '');
        input.id = newId;
        const label = cloned.querySelector('label');
        label.setAttribute('for', newId);

        const pickr = input.dataset.calendarType;
        if (pickr === 'date') {
            flatpickr(input, {'dateFormat' : 'd.m.Y', 'allowInput' : true});
        }
        if (pickr === 'datetime') {
            flatpickr(input, {'enableTime' : true, 'time_24hr' : true, 'dateFormat' : 'd.m.Y H:i', 'allowInput' : true});
        }
        if (pickr === 'time') {
            flatpickr(input, {'noCalendar' : true, 'enableTime' : true, 'time_24hr' : true, 'allowInput' : true});
        }

        elem.parentNode.parentNode.after(cloned);
        elem.parentNode.removeChild(elem);
    }
}
