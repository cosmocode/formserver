import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';
import {U} from '../U.js';
import {ValidatorError} from '../ValidatorError.js';

export class DateComponent extends BaseComponent {

    html() {
        const field = U.createField(this.config);

        const control = document.createElement("div");
        control.classList.add("control");

        field.appendChild(control);

        const input = document.createElement("input");
        input.type = "date";
        input.name = this.name;
        input.classList.add("input");
        input.placeholder = this.config.placeholder || "";
        input.value = this.myState.value ?? null;
        input.readOnly = !!this.config.readonly;

        control.appendChild(input);

        return field;
    }

    /**
     * Override to apply additional type specific validators
     */
    executeValidators() {
        super.executeValidators();

        if (!this.config.validation) {
            return;
        }

        const value = this.myState.value;
        if (!value) {
            return;
        }

        // start validation
        if (this.config.validation.start && new Date(value) < new Date(this.config.validation.start)) {
            throw new ValidatorError(this.name, `${U.getLang("error_start")} ${this.config.validation.start}`);
        }

        // end validation
        if (this.config.validation.end && new Date(value) > new Date(this.config.validation.end)) {
            throw new ValidatorError(this.name, `${U.getLang("error_end")} ${this.config.validation.end}`);
        }
    }
}

customElements.define('date-component', DateComponent);
