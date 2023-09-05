import {init as spinit} from "./sigpad.js";
import {initSignaturePad} from "./sigpad.js";

spinit();
initSignaturePad();

/**
 * Init flatpickr
 */
flatpickr('input:read-write[data-calendar-type="date"]', {'dateFormat' : 'd.m.Y', 'allowInput' : true});
flatpickr('input:read-write[data-calendar-type="time"]', {'noCalendar' : true, 'enableTime' : true, 'time_24hr' : true, 'allowInput' : true});
flatpickr('input:read-write[data-calendar-type="datetime"]', {'enableTime' : true, 'time_24hr' : true, 'dateFormat' : 'd.m.Y H:i', 'allowInput' : true});




/**
 * Init Toggle
 */
const fieldsetsWithToggle = document.querySelectorAll('[data-toggle-id]');

// Add event listener for every fieldset with toggle
Array.from(fieldsetsWithToggle).forEach(function(fieldset) {
    const formInput = getToggleFormInput(fieldset);
    formInput.addEventListener('change', function(e) {
        const formInput = e.target;
        toggleFieldset(fieldset, formInput);
    });
});

// Init fieldset states on page load
Array.from(fieldsetsWithToggle).forEach(function(fieldset) {
    const formInput = getToggleFormInput(fieldset);
    toggleFieldset(fieldset, formInput);

});

// Init upload feedback text
Array.from(document.querySelectorAll('.form-input.file-input')).forEach(function(fileUpload) {
    fileUpload.addEventListener('input', function (e) {
        const infoContainerId = e.target.getAttribute('data-info-container-id');
        const infoContainer = document.getElementById(infoContainerId);
        infoContainer.classList.remove('hidden');
    })
});

// Helper function to enable or disable a fieldset
function toggleFieldset(fieldset, formInput) {
    const toggleValue = fieldset.getAttribute('data-toggle-value');

    if (getFormInputValue(formInput) === toggleValue) {
        fieldset.removeAttribute('disabled');
        fieldset.classList.remove('hidden');
        Array.from(fieldset.querySelectorAll('.form-input')).forEach(function(fieldsetFormElement) {
            fieldsetFormElement.dispatchEvent(new Event("change"));
        });
    } else {
        fieldset.setAttribute('disabled', '');
        fieldset.classList.add('hidden');
        Array.from(fieldset.querySelectorAll('.form-input')).forEach(function(fieldsetFormElement) {
            clearFormElementValue(fieldsetFormElement);
            // Trigger event in case another fieldset is affected due to this toggle
            fieldsetFormElement.dispatchEvent(new Event("change"));
        });
    }
}

// Helper function to get form input
// This function is necessary for radios and checkboxes as they are special
function getToggleFormInput(fieldset) {
    const toggleId = fieldset.getAttribute('data-toggle-id');
    const toggleValue = fieldset.getAttribute('data-toggle-value');

    const formInput = document.getElementById(toggleId);
    if (formInput.tagName.toLowerCase() !== 'div') {
        return formInput;
    } else {
        let divFormInput;
        Array.from(formInput.querySelectorAll('.form-input')).forEach(function(formElement) {
            if (formElement.value === toggleValue) {
                divFormInput = formElement;
            } else if (formElement.type === 'radio') {
                formElement.addEventListener('click', function(e) {
                    divFormInput.dispatchEvent(new Event("change"));
                });
            }
        });

        return divFormInput;
    }

}

// Helper function to get input value
// This function is necessary for radios and checkboxes as they always have a value
// which gets only returned if this formInput is checked
function getFormInputValue(formInput) {
    if (formInput.type === 'checkbox' || formInput.type === 'radio') {
        if (formInput.checked) {
            return formInput.value;
        }

        return '';
    }

    return formInput.value;
}

// Helper function to unset input value
// This function is necessary for radios and checkboxes as they are unset via property 'checked'
function clearFormElementValue(formInput) {
    if (formInput.type === 'checkbox' || formInput.type === 'radio') {
        formInput.checked = false;
    } else {
        formInput.value = '';
    }
}

/*
 * Table-like style
 * Double the width of the first column to fit both labels and input fields
 */
Array.from(document.querySelectorAll('.is-left-label .next-is-double')).forEach(function(elem) {
    const colThird = elem.nextElementSibling.querySelector('.is-one-third');
    if (colThird) {
        colThird.classList.remove('is-one-third');
        colThird.classList.add('is-two-thirds');
    }
    const colQuarter = elem.nextElementSibling.querySelector('.is-one-quarter');
    if (colQuarter) {
        colQuarter.classList.remove('is-one-quarter');
        colQuarter.classList.add('is-two-quarters');
    }
    const colFifth = elem.nextElementSibling.querySelector('.is-one-fifth');
    if (colFifth) {
        colFifth.classList.remove('is-one-fifth');
        colFifth.classList.add('is-two-fifths');
    }
});
