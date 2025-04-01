import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';
import {U} from '../U.js';
import {ValidatorError} from "../ValidatorError";

export class TextinputComponent extends BaseComponent {

    html() {
        const field = document.createElement("div");
        field.classList.add("field");

        const label = document.createElement("label");
        label.classList.add("label")
        label.innerText = html`${this.config.label}` + U.requiredMark(this.config);


        field.appendChild(label);
        field.insertAdjacentHTML('beforeend', U.tooltipHint(this.config));
        if (this.config.modal) {
            field.appendChild(U.modalHint(this.config));
            field.appendChild(U.modal(this.config));
        }

        const control = document.createElement("div");
        control.classList.add("control");

        field.appendChild(control);

        const input = document.createElement("input");
        input.type = "text";
        input.name = this.config.name;
        input.classList.add("input");
        input.placeholder = this.config.placeholder || "";
        input.value = this.myState.value ?? null;
        input.readOnly = !!this.config.readonly;

        control.appendChild(input);

        return field;
    }


    /**
     * Handle "match" validation
     */
    executeValidators() {
        super.executeValidators();

        if (
            this.config.validation && this.config.validation.match
        ) {
            const pattern = this.config.validation.match.replace(/^\/|\/$/g, '');
            const regex = new RegExp(pattern);
            // a null value is internally converted into the string "null", so replace it with ""
            if (regex.test(this.myState.value || "") === false) {
                throw new ValidatorError(this.name, U.getLang("error_match"));
            }
        }
    }

}

customElements.define('textinput-component', TextinputComponent);
