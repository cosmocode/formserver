import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';
import {U} from '../U.js';
import {ValidatorError} from "../ValidatorError";

export class NumberinputComponent extends BaseComponent {

    html() {
        return html`
            <div class="field">
                <label class="label">${this.config.label}${U.requiredMark(this.config)}</label>$${U.tooltipHint(this.config)}
                <div class="control">
                    <input class="input" type="number" step="1" 
                           name="${this.name}" value="${this.myState.value ?? ''}"
                           ${this.config.readonly ? 'readonly' : ''}>
                </div>
            </div>
        `;
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
