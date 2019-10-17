/**
 * Init flatpickr
 */
flatpickr('[data-calendar-type="date"]', {'dateFormat' : 'd.m.Y', 'allowInput' : true});
flatpickr('[data-calendar-type="time"]', {'noCalendar' : true, 'enableTime' : true, 'time_24hr' : true, 'allowInput' : true});
flatpickr('[data-calendar-type="datetime"]', {'enableTime' : true, 'time_24hr' : true, 'dateFormat' : 'd.m.Y H:i', 'allowInput' : true});


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
