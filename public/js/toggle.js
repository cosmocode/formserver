/**
 * Init Toggle
 */
export function initToggles() {
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
}
