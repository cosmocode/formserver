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

    #form;
    #state;
    #formConfig;

    constructor() {
        super();

        this.#form = document.createElement('form');
        this.appendChild(this.#form);
        this.#formConfig = U.loadFormConfig();
        this.#state = new State(U.loadFormState());
    }

    connectedCallback() {
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

    attachButtons(form) {
        const formControlButtons = document.createElement('div');
        formControlButtons.innerHTML =
            `<div class="field is-grouped has-addons has-addons-centered">
                <div class="control">
                    <button class="button is-link" type="submit" name="save">
                        ${U.getLang("button_save")}
                    </button>
                </div>
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

        this.#displayFormStatusNotofication(isValid);

        console.log('SUBMIT', this.#state.values);
        console.log('SUBMIT VALIDATION', isValid);

        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                "mode": event.submitter.name, /* submit or save */
                "data": this.#state.values
            }),
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                console.log('Success:', data);
            })
            .catch((error) => {
                console.error('Error:', error);
            });

    }

    #displayFormStatusNotofication(isValid) {
        const notification = this.querySelector('.notification');

        if (!isValid) {
            notification.classList.add('is-danger');
            notification.classList.remove('is-success');
            notification.innerHTML = `<p>${U.getLang("form_invalid")}</p>`;
        } else {
            notification.classList.remove('is-danger');
            notification.classList.add('is-success');
            notification.innerHTML = `<p>${U.getLang("form_valid")}</p>`;
        }

        notification.style.display = 'block';

    }
}

customElements.define('form-component', FormComponent);
