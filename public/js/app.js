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


/**
 * Init Toggle
 */
var fieldsetsWithToggle = document.querySelectorAll('[data-toggle-id]');

// Add event listener for every fieldset with toggle
Array.from(fieldsetsWithToggle).forEach(function(fieldset) {
    var formInput = document.getElementById(fieldset.getAttribute('data-toggle-id'));

    formInput.addEventListener('change', function(e) {
        var formInput = e.target;
        toggleFieldset(fieldset, formInput);
    });
});

// Init fieldset states on page load
Array.from(fieldsetsWithToggle).forEach(function(fieldset) {
    var formInput = document.getElementById(fieldset.getAttribute('data-toggle-id'));
    toggleFieldset(fieldset, formInput);

});

// Helper function to enable or disable a fieldset
function toggleFieldset(fieldset, formInput) {
    var toggleValue = fieldset.getAttribute('data-toggle-value');

    if (formInput.value == toggleValue) {
        fieldset.removeAttribute('disabled');
        fieldset.classList.remove('hidden');
        Array.from(fieldset.querySelectorAll('.form-input')).forEach(function(fieldsetFormElements) {
            fieldsetFormElements.dispatchEvent(new Event("change"));
        });
    } else {
        fieldset.setAttribute('disabled', '');
        fieldset.classList.add('hidden');
        Array.from(fieldset.querySelectorAll('.form-input')).forEach(function(fieldsetFormElements) {
            fieldsetFormElements.value = '';
            // Trigger event in case another fieldset is affected due to this toggle
            fieldsetFormElements.dispatchEvent(new Event("change"));
        });
    }
}