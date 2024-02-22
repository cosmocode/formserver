/**
 * Init flatpickr
 */
flatpickr('input:read-write[data-calendar-type="date"]', {'dateFormat' : 'd.m.Y', 'allowInput' : true});
flatpickr('input:read-write[data-calendar-type="time"]', {'noCalendar' : true, 'enableTime' : true, 'time_24hr' : true, 'allowInput' : true});
flatpickr('input:read-write[data-calendar-type="datetime"]', {'enableTime' : true, 'time_24hr' : true, 'dateFormat' : 'd.m.Y H:i', 'allowInput' : true});


/**
 * Init Signature Pad
 */
var form = document.getElementById("form");
var replaceButons = document.querySelectorAll('button.sigreplace');
var wrappers = document.querySelectorAll(".signature-pad");

// attach event listeners to replace buttons
Array.from(replaceButons).forEach(function(button) {
    button.addEventListener('click', function(event) {
        var id = this.dataset.replacebutton;

        var replacePad = document.querySelector('[data-replacepad=' + id + ']');
        replacePad.classList.remove('hidden');
        initSignaturePad(replacePad);

        var replaceContainer = document.querySelector('[data-replacecontainer=' + id + ']');
        replaceContainer.classList.add('hidden');
    });
});

// initialize all (visible) fields
Array.from(wrappers).forEach(function(wrapper) {
    initSignaturePad(wrapper);
});

/**
 * Initializes a clean signature pad for each visible signature element
 *
 * @param wrapper
 */
function initSignaturePad(wrapper) {
    var canvas = wrapper.querySelector("canvas");
    var clearButton = wrapper.querySelector("[data-action=clear]");
    var dataField = wrapper.querySelector("input");

    if (wrapper && canvas && !wrapper.classList.contains('hidden')) {
        // reset field value
        dataField.value = "";
        var signaturePad = new SignaturePad(canvas, { backgroundColor: "rgb(255,255,255)" });

        // Set height and width
        width = wrapper.dataset.width;
        if (width) {
            canvas.width = width;
        }
        height = wrapper.dataset.height;
        if (height) {
            canvas.height = height;
        }

        // Add event listeners
        clearButton.addEventListener("click", function (event) {
            signaturePad.clear();
            dataField.value = "";
        });

        form.addEventListener("submit", function (event) {
            if (signaturePad.isEmpty()) {
                dataField.value = '';
            } else {
                dataField.value = signaturePad.toDataURL();
            }
        });
    }
}

/**
 * Init Toggle
 */
var fieldsetsWithToggle = document.querySelectorAll('[data-toggle-id]');

// Add event listener for every fieldset with toggle
Array.from(fieldsetsWithToggle).forEach(function(fieldset) {
    var formInput = getToggleFormInput(fieldset);
    formInput.addEventListener('change', function(e) {
        var formInput = e.target;
        toggleFieldset(fieldset, formInput);
    });
});

// Init fieldset states on page load
Array.from(fieldsetsWithToggle).forEach(function(fieldset) {
    var formInput = getToggleFormInput(fieldset);
    toggleFieldset(fieldset, formInput);

});

// Init upload feedback text
Array.from(document.querySelectorAll('.form-input.file-input')).forEach(function(fileUpload) {
    fileUpload.addEventListener('input', function (e) {
        const infoContainerId = e.target.getAttribute('data-info-container-id');
        const infoContainer = document.getElementById(infoContainerId);
        const errorContainerId = e.target.getAttribute('data-error-container-id');
        const errorContainer = document.getElementById(errorContainerId);

        const max = e.target.getAttribute('data-max');
        const curFiles = this.files;

        let uploadSize = 0;

        for (const file of curFiles) {
            uploadSize += file.size;
        }

        const ok = uploadSize < max;

        if (!ok) {
            this.value = ''
            errorContainer.classList.remove('hidden');
            infoContainer.classList.add('hidden');
        } else {
            errorContainer.classList.add('hidden');
            infoContainer.classList.remove('hidden');

        }

    })
});

// Helper function to enable or disable a fieldset
function toggleFieldset(fieldset, formInput) {
    var toggleValue = fieldset.getAttribute('data-toggle-value');

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
    var toggleId = fieldset.getAttribute('data-toggle-id');
    var toggleValue = fieldset.getAttribute('data-toggle-value');

    formInput = document.getElementById(toggleId);
    if (formInput.tagName.toLowerCase() !== 'div') {
        return formInput;
    } else {
        var divFormInput;
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
    colThird = elem.nextElementSibling.querySelector('.is-one-third');
    if (colThird) {
        colThird.classList.remove('is-one-third');
        colThird.classList.add('is-two-thirds');
    }
    colQuarter = elem.nextElementSibling.querySelector('.is-one-quarter');
    if (colQuarter) {
        colQuarter.classList.remove('is-one-quarter');
        colQuarter.classList.add('is-two-quarters');
    }
    colFifth = elem.nextElementSibling.querySelector('.is-one-fifth');
    if (colFifth) {
        colFifth.classList.remove('is-one-fifth');
        colFifth.classList.add('is-two-fifths');
    }
});


/*
 * Clone field
 */
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

form.addEventListener('click', cloneHandler);

/**
 * Move tooltips inside headings for better styling
 */
Array.from(document.querySelectorAll('.with-tooltip .control button.tooltip-button')).forEach(function(elem) {
    const prev = elem.previousElementSibling;
    const headlines = ['H1', 'H2', 'H3', 'H4', 'H5', 'H6'];
    if (headlines.includes(prev.tagName)) {
        prev.appendChild(elem);
    }
});

/**
 * Modal
 */
document.addEventListener('DOMContentLoaded', () => {
    // Functions to open and close a modal
    function openModal($el) {
        $el.classList.add('is-active');
        document.querySelector('html').classList.add('is-clipped');
    }

    function closeModal($el) {
        $el.classList.remove('is-active');
        document.querySelector('html').classList.remove('is-clipped');
    }

    function closeAllModals() {
        (document.querySelectorAll('.modal') || []).forEach(($modal) => {
            closeModal($modal);
        });
    }

    // Add a click event on buttons to open a specific modal
    (document.querySelectorAll('.js-modal-trigger') || []).forEach(($trigger) => {
        const modal = $trigger.dataset.target;
        const $target = document.getElementById(modal);

        $trigger.addEventListener('click', () => {
            openModal($target);
        });
    });

    // Add a click event on various child elements to close the parent modal
    (document.querySelectorAll('.modal-background, .modal-close, .modal-card-head .delete, .modal-card-foot .button') || []).forEach(($close) => {
        const $target = $close.closest('.modal');

        $close.addEventListener('click', (evt) => {
            evt.preventDefault();
            closeModal($target);
        });
    });

    // Add a keyboard event to close all modals
    document.addEventListener('keydown', (event) => {
        if(event.key === "Escape") {
            closeAllModals();
        }
    });
});
