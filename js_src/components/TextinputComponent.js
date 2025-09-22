import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';
import {ValidatorError} from "../ValidatorError";
import htmlTpl from "html-template-tag";

export class TextinputComponent extends BaseComponent {

    html() {
        let additionalClasses = [];
        let suppressLabel = false;
        let controlElement = "div";
        let controlClasses = ["control"];

        // Bulma needs alternative HTML for suffixed fields
        if (this.config.suffix) {
            additionalClasses = ["has-addons"];
            suppressLabel = true;
            controlElement = "p";
            controlClasses.push("is-expanded");
        }

        const field = U.createField(this.config, additionalClasses, false, suppressLabel);

        const control = document.createElement(controlElement);
        control.classList.add(...controlClasses);

        field.appendChild(control);

        const input = document.createElement("input");
        input.type = "text";
        input.name = this.config.name;
        input.classList.add("input");
        input.placeholder = this.config.placeholder || "";
        input.value = this.myState.value ?? null;
        input.readOnly = !!this.config.readonly;

        control.appendChild(input);

        // append the suffix pseudo-button
        if (this.config.suffix) {
            const suffix = document.createElement("p");
            suffix.classList.add("control");
            suffix.innerHTML = `<a class="button is-static">${this.config.suffix}</a>`;
            field.appendChild(suffix);
        }

        return field;
    }

    /**
     * Override is necessary for suffixed fields, only because Bulma requires specific DOM structure
     * for field.has-addons - label must be placed in the wrapper, before the field itself.
     *
     * @param html
     * @returns {HTMLDivElement}
     */
    htmlWrap(html) {
        const wrapper = super.htmlWrap(html);

        if (this.config.suffix) {
            const label = document.createElement("label");
            label.classList.add("label");
            if (this.config.labelsmall) {
                label.classList.add("label-smaller");
            }
            label.innerText = htmlTpl`${this.config.label}` + U.requiredMark(this.config);
            wrapper.insertAdjacentElement("afterbegin", label);
        }

        return wrapper;
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
