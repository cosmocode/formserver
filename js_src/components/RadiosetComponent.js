import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';

export class RadiosetComponent extends BaseComponent {

    /** @override */
    html() {
        const radioset = document.createElement('div');
        radioset.classList.add('field');

        const label = document.createElement('label');
        label.classList.add('label');
        label.innerText = this.config.label + U.requiredMark(this.config);
        radioset.appendChild(label);

        const tooltip = U.tooltipHint(this.config);
        radioset.insertAdjacentHTML('beforeend', tooltip);

        if (this.config.modal) {
            radioset.appendChild(U.modalHint(this.config));
            radioset.appendChild(U.modal(this.config));
        }

        const control = document.createElement('div');
        control.classList.add('control');
        radioset.appendChild(control);

        for (const option of this.config.choices) {
            control.insertAdjacentHTML("beforeend", this.htmlRadioElement(option));
        }

        return radioset;
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
