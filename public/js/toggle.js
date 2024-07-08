/**
 * Init Toggle
 */
export function initToggles() {
    /**
     * Change handler for the trigger element: updates state of options and clears the selected value.
     *
     * @param option
     * @param parent parent element containing all options
     * @param triggerElement
     * @param toggleValue specific to this conditional option
     */
    function resetConditional(option, parent, triggerElement, toggleValue) {
        if (toggleValue.includes(getFormInputValue(triggerElement))) {
            option.removeAttribute('disabled');
            option.removeAttribute('hidden');
        } else {
            option.setAttribute('disabled', true);
            option.setAttribute('hidden', true);
            option.setAttribute('selected', false);
            option.setAttribute('checked', false);
        }
        clearFormElementValue(parent);
    }

    const fieldsetsWithToggle = document.querySelectorAll('fieldset[data-toggle-id]');
    const optionsWithToggle = document.querySelectorAll('option[data-toggle-id]');

    // Init fieldset states on page load and add event listeners
    Array.from(fieldsetsWithToggle).forEach(function(fieldset) {
        const toggleId = fieldset.getAttribute('data-toggle-id');
        const toggleValue = JSON.parse(fieldset.getAttribute('data-toggle-value'));
        const formInput = getToggleFormInput(toggleId, toggleValue);
        toggleFieldset(fieldset, formInput, formInput.checkVisibility());
        formInput.addEventListener('change', function(e) {
            const formInput = e.target;
            toggleFieldset(fieldset, formInput, formInput.checkVisibility());
        });
    });

    // Init state of conditional select options on page load
    // and add change handler
    Array.from(optionsWithToggle).forEach(function(option) {
        const parent = option.parentElement;
        const toggleId = option.getAttribute('data-toggle-id');
        const toggleValue = JSON.parse(option.getAttribute('data-toggle-value'));
        const triggerElement = getToggleFormInput(toggleId, toggleValue);

        if (toggleValue.includes(getFormInputValue(triggerElement))) {
            option.removeAttribute('disabled');
            option.removeAttribute('hidden');
        } else {
            option.removeAttribute('selected');
        }

        triggerElement.addEventListener('change', function(e) {
            resetConditional(option, parent, triggerElement, toggleValue);
        });
    });

    // clear all inputs in hidden fields on submit
    form.addEventListener("submit", function (event) {
        Array.from(fieldsetsWithToggle).forEach(function(fieldset) {
            if (!fieldset.classList.contains('hidden')) {
                return;
            }
            Array.from(fieldset.querySelectorAll('.form-input')).forEach(function(fieldsetFormElement) {
                clearFormElementValue(fieldsetFormElement);
            });
        });
    });

    /**
     * Helper function to enable or disable a fieldset
     *
     * @param fieldset Fieldset to toggle
     * @param formInput Toggle trigger
     * @param isVisible False if the toggle trigger is not visible (e.g. itself in a hidden fieldset)
     */
    function toggleFieldset(fieldset, formInput, isVisible) {
        const toggleValue = JSON.parse(fieldset.getAttribute('data-toggle-value'));

        // check if toggleValue matches and is not hidden / in a disabled fieldset
        if (isVisible && toggleValue.includes(getFormInputValue(formInput))) {
            fieldset.removeAttribute('disabled');
            fieldset.classList.remove('hidden');
            Array.from(fieldset.querySelectorAll('.form-input')).forEach(function(fieldsetFormElement) {
                fieldsetFormElement.dispatchEvent(new Event("change"));
            });
        } else {
            fieldset.setAttribute('disabled', '');
            fieldset.classList.add('hidden');
            Array.from(fieldset.querySelectorAll('.form-input')).forEach(function(fieldsetFormElement) {
                // Trigger event in case another fieldset is affected due to this toggle
                fieldsetFormElement.dispatchEvent(new Event("change"));
            });
        }
    }

    // Helper function to get form input
    // This function is necessary for radios and checkboxes as they are special
    function getToggleFormInput(toggleId, toggleValue) {
        const formInput = document.getElementById(toggleId);
        if (formInput.tagName.toLowerCase() !== 'div') {
            return formInput;
        } else {
            let divFormInput;
            Array.from(formInput.querySelectorAll('.form-input')).forEach(function(formElement) {
                if (toggleValue.includes(formElement.value)) {
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
