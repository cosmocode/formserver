import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';
import {ValidatorError} from "../ValidatorError";

export class TextinputComponent extends BaseComponent {

    html() {
        const field = U.createField(this.config);

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
     * Handle text specific validation rules
     */
    executeValidators() {
        super.executeValidators();

        if (!this.config.validation) {
            return;
        }

        if (this.config.validation.match) {
            const pattern = this.config.validation.match.replace(/^\/|\/$/g, '');
            const regex = new RegExp(pattern);
            // a null value is internally converted into the string "null", so replace it with ""
            if (regex.test(this.myState.value || "") === false) {
                throw new ValidatorError(this.name, U.getLang("error_match"));
            }
        }

        if (
            this.config.validation.maxlength &&
            this.myState.value &&
            this.myState.value.length > this.config.validation.maxlength
        ) {
            throw new ValidatorError(this.name, `${U.getLang("error_maxlength")} ${this.config.validation.maxlength}`);
        }
    }

}

customElements.define('textinput-component', TextinputComponent);
