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

    #name;
    #form;
    #state;
    #formConfig;

    constructor() {
        super();

        this.#form = document.createElement('form');
        this.appendChild(this.#form);
        this.#formConfig = U.loadFormConfig();
    }

    async connectedCallback() {
        this.#name = this.getAttribute("formId");

        // stored values have precedence over initial values from backend
        let initialValues = await State.getValuesFromOPFS(this.#name);

        if (!Object.keys(initialValues).length) {
            initialValues = U.loadFormValues(); // loads "values" part of JSON config
        }

        this.#state = new State(this.#name, initialValues);
        this.render();
    }

    render() {
        const errorContainer = document.createElement('div');
        errorContainer.classList.add('notification');
        errorContainer.style.display = 'none';
        this.prepend(errorContainer);

        this.attachElements(this.#form);
        this.attachButtons(this.#form);

        this.#form.addEventListener('submit', this.handleSubmit.bind(this));
    }

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
    }

    validate() {
        const components = this.querySelectorAll('.component');
        let ok = true;

        components.forEach(component => {
            ok &= component.validate();
        });

        return ok;
    }


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
                    this.#state.clearOPFS(this.#name);
                }
            })
            .catch((error) => {
                this.#displayFormStatusNotification("send_failed");
                console.error('Error:', error);
            });
    }

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
}

customElements.define('form-component', FormComponent);
