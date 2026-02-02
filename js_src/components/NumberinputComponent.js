import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';
import {U} from '../U.js';
import {ValidatorError} from "../ValidatorError";
import htmlTpl from "html-template-tag";

export class NumberinputComponent extends BaseComponent {

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
        input.type = "number";
        input.step = 1;
        input.name = this.name;
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
