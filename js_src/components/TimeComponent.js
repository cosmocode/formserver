import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';
import {U} from '../U.js';
import {ValidatorError} from '../ValidatorError.js';

export class TimeComponent extends BaseComponent {
    html() {
        const field = U.createField(this.config);

        const control = document.createElement("div");
        control.classList.add("control");

        field.appendChild(control);

        const input = document.createElement("input");
        input.type = "time";
        input.name = this.name;
        input.classList.add("input");
        input.placeholder = this.config.placeholder || "";
        input.value = this.myState.value ?? null;
        input.readOnly = !!this.config.readonly;

        control.appendChild(input);

        return field;
    }

    executeValidators() {
        super.executeValidators();

        if (!this.config.validation) {
            return;
        }

        const value = this.myState.value;
        if (!value) {
            return;
        }

        // Check start time validation
        if (this.config.validation.start && value < this.config.validation.start) {
            throw new ValidatorError(this.name, `${U.getLang("error_start")} ${this.config.validation.start}`);
        }

        // Check end time validation
        if (this.config.validation.end && value > this.config.validation.end) {
            throw new ValidatorError(this.name, `${U.getLang("error_end")} ${this.config.validation.end}`);
        }
    }
}

customElements.define('time-component', TimeComponent);
