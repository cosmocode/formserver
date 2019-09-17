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
var wrappers = document.querySelectorAll(".signature-pad");

Array.from(wrappers).forEach(function(wrapper) {
    var canvas = wrapper.querySelector("canvas");
    var clearButton = wrapper.querySelector("[data-action=clear]");
    var dataField = wrapper.querySelector("input");

    if (wrapper && canvas) {
        var signatureData = dataField.value;
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

        // Insert stored data
        if (signatureData !== '') {
            signaturePad.fromDataURL(signatureData);
        }

        // Add event listener
        clearButton.addEventListener("click", function (event) {
            signaturePad.clear();
            dataField.value = "";
        });

        form.addEventListener("submit", function (event) {
            dataField.value = signaturePad.toDataURL("image/svg+xml");
        });
    }
});


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
            fieldsetFormElement.value = '';
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
