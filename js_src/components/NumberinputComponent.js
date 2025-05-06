import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';
import {U} from '../U.js';
import {ValidatorError} from "../ValidatorError";

export class NumberinputComponent extends BaseComponent {

    html() {
        const field = U.createField(this.config);

        const control = document.createElement("div");
        control.classList.add("control");

        field.appendChild(control);

        const input = document.createElement("input");
        input.type = "number";
        input.step = 1;
        input.name = this.name;
        input.classList.add("input");
        input.placeholder = this.config.placeholder || "";
        input.value = this.myState.value ?? null;
        input.readOnly = !!this.config.readonly;

        control.appendChild(input);

        return field;
    }

    /**
     * Handle "min" and "max" validation
     */
    executeValidators() {
        super.executeValidators();

        if (
            this.config.validation && this.config.validation.min && this.myState.value &&
            this.myState.value < this.config.validation.min
        ) {
            throw new ValidatorError(this.name, `${U.getLang("error_min")} ${this.config.validation.min}`);
        }

        if (
            this.config.validation && this.config.validation.max && this.myState.value &&
            this.myState.value > this.config.validation.max
        ) {
            throw new ValidatorError(this.name, `${U.getLang("error_max")} ${this.config.validation.max}`);
        }
    }
}

customElements.define('numberinput-component', NumberinputComponent);
