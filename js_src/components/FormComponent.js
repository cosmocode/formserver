import {U} from '../U.js';
import {State} from "../State";

/**
 * Ideas
 *
 * register an event handler onChange or maybe onInput on the Form Component
 * when a change is detected, update the data using the JS FormData Api
 *   - this might happen automatically if we initialize FormData with the form element once
 *   - alternatively we could also maybe do a diff between the old and new form data
 * trigger a custom event on the FormComponent
 * the BaseField can find the FormComponent by traversing the DOM upwards on initialization
 * the BaseField should listen to the custom event on the FormComponent
 *   - maybe even only when needed, eg. when there are any dependencies
 * this event would trigger several things:
 *   - general dependency checks (like should the component be visible or not)
 *   - validation checks
 *   - dependency checks for dropdown options
 *   - generally we do not really need to completely rerender here, though we could use dom diffing
 *
 * questions:
 *   - how would we deal with initial data, when we use a FormData object?
 *   - child elements like clone or table, need the initial data to know how many copies to render
 */

export class FormComponent extends HTMLElement {

    /** @type {string} The form identifier */
    #name;
    /** @type {HTMLFormElement} The form DOM element */
    #form;
    /** @type {State} The form state manager */
    #state;
    /** @type {object} The form configuration object */
    #formConfig;

    constructor() {
        super();

        this.#form = document.createElement('form');
        this.appendChild(this.#form);
        this.#formConfig = U.loadFormConfig();
    }

    /**
     * Called when the element is connected to the DOM
     */
    async connectedCallback() {
        this.#name = this.getAttribute("formId");

        const initialValues = U.loadFormValues(); // loads "values" part of JSON config
        this.#state = new State(this.#name, initialValues, this.render.bind(this));
    }

    /**
     * Renders the form with all its components and buttons
     */
    render() {
        const errorContainer = document.createElement('div');
        errorContainer.classList.add('notification');
        errorContainer.style.display = 'none';
        this.prepend(errorContainer);

        this.attachElements(this.#form);
        this.attachButtons(this.#form);

        this.#form.addEventListener('submit', this.handleSubmit.bind(this));
    }

    /**
     * Attaches form elements to the parent container
     * @param {HTMLElement} parent The parent element to attach children to
     */
    attachElements(parent) {
        U.attachChildren(parent, this.#formConfig, this.#state);
    }

    /**
     * Attach submit buttons to the end of the form.
     * "Send" is always used, "Save" is present by default but can be disabled in YAML canfig.
     *
     * @param {HTMLElement} form
     */
    attachButtons(form) {
        // optional save button
        const saveButtonHTML = `<div class="control">
                    <button class="button is-link" type="submit" name="save">
                        ${U.getLang("button_save")}
                    </button>
                </div>`;

        const formControlButtons = document.createElement('div');

        formControlButtons.innerHTML =
            `<div class="field is-grouped has-addons has-addons-centered">
               ${U.formMeta("saveButton") !== false ? saveButtonHTML : ''}
                <div class="control">
                    <button class="button is-link" type="submit" name="send">
                        ${U.getLang("button_send")}
                    </button>
                </div>
            </div>`;
        form.appendChild(formControlButtons);

        // add offline/online event listeners
        this.#handleOfflineState(formControlButtons);
    }

    /**
     * Validates all form components
     * @returns {boolean} True if all components are valid, false otherwise
     */
    validate() {
        const components = this.querySelectorAll('.component');
        let ok = true;

        components.forEach(component => {
            ok &= component.validate();
        });

        return ok;
    }


    /**
     * Handles form submission
     * @param {Event} event The submit event
     */
    handleSubmit(event) {
        event.preventDefault();

        const isValid = this.validate();

        console.log('SUBMIT', this.#state.values);
        console.log('SUBMIT valid?', isValid);

        if (event.submitter.name === "send" && !isValid) {
            this.#displayFormStatusNotification("send_prevented");
            return;
        }

        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                "mode": event.submitter.name, /* submit or save */
                "data": U.convertSetsToArray(this.#state.values)
            }),
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                // FIXME this is not real status from response!
                const status = !isValid ? "form_invalid" : (event.submitter.name === "send" ? "send_success" : "form_valid");
                this.#displayFormStatusNotification(status);
                if (status === "send_success") {
                    this.#state.clearOPFS();
                }
            })
            .catch((error) => {
                this.#displayFormStatusNotification("send_failed");
                console.error('Error:', error);
            });
    }

    /**
     * Displays a status notification to the user
     * @param {string} status The status key for the notification message
     */
    #displayFormStatusNotification(status) {
        const notification = this.querySelector('.notification');

        switch (status) {
            case "send_prevented":
            case "send_failed":
            case "form_invalid":
                notification.classList.add('is-danger');
                notification.classList.remove('is-success');
                notification.innerHTML = `<p>${U.getLang(status)}</p>`;
                break;
            case "form_valid":
            case "send_success":
                notification.classList.add('is-success');
                notification.classList.remove('is-danger');
                notification.innerHTML = `<p>${U.getLang(status)}</p>`;
                break;
            default:
                console.log(`Unknown status: "${status}."`);
        }

        notification.style.display = 'block';
        notification.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /**
     * Handles offline/online state changes and updates button availability
     * @param {HTMLElement} formControlButtons The container with form control buttons
     */
    #handleOfflineState(formControlButtons) {
        const updateButtonState = () => {
            const buttons = formControlButtons.querySelectorAll('button');
            const isOffline = !navigator.onLine;

            buttons.forEach(button => {
                button.disabled = isOffline;
            });

            let offlineMessage = formControlButtons.querySelector('.offline-message');
            if (isOffline) {
                if (!offlineMessage) {
                    offlineMessage = document.createElement('div');
                    offlineMessage.classList.add('notification', 'is-warning', 'offline-message');
                    offlineMessage.innerHTML = `<div class="icon-text">
                            <span class="icon">
                                <img src="/images/cloud-off-outline.svg" alt="Offline">
                            </span>
                            <span>${U.getLang("offline")}</span>
                        </div>`;
                    formControlButtons.insertBefore(offlineMessage, formControlButtons.firstChild);
                }
                offlineMessage.style.display = 'block';
            } else {
                if (offlineMessage) {
                    offlineMessage.style.display = 'none';
                }
            }
        };

        // Initial state check
        updateButtonState();

        // Listen for online/offline events
        window.addEventListener('online', updateButtonState);
        window.addEventListener('offline', updateButtonState);
    }
}

customElements.define('form-component', FormComponent);
