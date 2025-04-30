import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';

export class RadiosetComponent extends BaseComponent {

    /** @override */
    html() {
        const field = U.createField(this.config);

        const control = document.createElement('div');
        control.classList.add('control');
        field.appendChild(control);

        for (const option of this.config.choices) {
            control.insertAdjacentHTML("beforeend", this.htmlRadioElement(option));
        }

        return field;
    }

    /**
     * Create a single option
     *
     * A string is required because HTMLElements are not indented and that
     * breaks spacing in Bulma https://github.com/jgthms/bulma/issues/1049
     *
     * @param {string} optionLabel
     * @returns {string}
     */
    htmlRadioElement(optionLabel) {
        const checked = (this.myState.value === optionLabel) ? 'checked' : '';
        return `
            <label class="radio ${this.config.readonly ? 'readonly-choices' : ''}">
                <input type="radio" name="${this.config.name}" value="${optionLabel}"
                    ${checked} />
                ${optionLabel}
            </label>${(this.config.alignment && this.config.alignment === 'vertical') ? '<br>' : ''}
        `;
    }
}

customElements.define('radioset-component', RadiosetComponent);
