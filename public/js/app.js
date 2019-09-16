// Inject flatpickr
flatpickr('[data-calendar-type="date"]', {'dateFormat' : 'd.m.Y', 'allowInput' : true});
flatpickr('[data-calendar-type="time"]', {'noCalendar' : true, 'enableTime' : true, 'time_24hr' : true, 'allowInput' : true});
flatpickr('[data-calendar-type="datetime"]', {'enableTime' : true, 'time_24hr' : true, 'dateFormat' : 'd.m.Y H:i', 'allowInput' : true});

/**
 * Signature Pad
 */

var form = document.getElementById("form");
var wrapper = document.getElementById("signature-pad");
var canvas = document.querySelector("canvas");
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
        dataField.value = signaturePad.toDataURL();
    });
}


// Toggle
var fieldsetsWithToggle = document.querySelectorAll('[data-toggle-id]');

// Add event listener for every fieldset with toggle
Array.from(fieldsetsWithToggle).forEach(function(fieldset) {
    var formInput = document.getElementById(fieldset.getAttribute('data-toggle-id'));

    formInput.addEventListener('change', function(e) {
        var formInput = e.target;
        var toggleValue = fieldset.getAttribute('data-toggle-value');

        // Target form input has correct value. Fieldset with children will be shown
        if (formInput.value == toggleValue) {
            fieldset.removeAttribute('disabled');
            fieldset.classList.remove('hidden');
            Array.from(fieldset.querySelectorAll('.form-input')).forEach(function(fieldsetFormElements) {
                fieldsetFormElements.dispatchEvent(new Event("change"));
            });
        // Target form input has another value. Disable and hide fieldset. Also clear its childrens value
        } else {
            fieldset.setAttribute('disabled', '');
            fieldset.classList.add('hidden');
            Array.from(fieldset.querySelectorAll('.form-input')).forEach(function(fieldsetFormElements) {
                fieldsetFormElements.value = '';
                fieldsetFormElements.dispatchEvent(new Event("change"));
            });
        }
    });
});

// Trigger event listener above to init fieldset states
Array.from(fieldsetsWithToggle).forEach(function(fieldset) {
    Array.from(fieldset.querySelectorAll('.form-input')).forEach(function (fieldsetFormElements) {
        console.log(fieldset);
        fieldsetFormElements.dispatchEvent(new Event("change"));
    });
});
