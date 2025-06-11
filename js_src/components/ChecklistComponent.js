import {BaseComponent} from './BaseComponent.js';
import {U} from "../U";
import {ComponentState} from "../ComponentState";

export class ChecklistComponent extends BaseComponent {

    /** @override */
    html() {
        const field = document.createElement('div');
        field.classList.add('field');

        const label = document.createElement('label');
        label.classList.add('label');
        if (this.config.labelsmall) {
            label.classList.add("label-smaller");
        }
        label.innerText = this.config.label + U.requiredMark(this.config);

        field.appendChild(label);

        const tooltip = U.tooltipHint(this.config);
        field.insertAdjacentHTML('beforeend', tooltip);

        if (this.config.modal) {
            field.appendChild(U.modalHint(this.config));
            field.appendChild(U.modal(this.config));
        }

        const control = document.createElement('div');
        control.classList.add('control');
        field.appendChild(control);

        // use literal choice items for values, but markdown transformed choices for labels
        let i = 0;
        for (i; i < this.config.transformed_choices.length; i++) {
            control.insertAdjacentHTML("beforeend", this.htmlCheckboxElement(this.config.transformed_choices[i], this.config.choices[i]));
        }

        return field;
    }

    /**
     * Create a single option.
     *
     * A string is required because HTMLElements are not indented and that
     * breaks spacing in Bulma https://github.com/jgthms/bulma/issues/1049
     *
     * @param {string} optionLabel
     * @param {string} optionValue
     * @returns {string}
     */
    htmlCheckboxElement(optionLabel, optionValue) {
        const checked = (this.myState.value && this.myState.value instanceof Set && this.myState.value.has(optionValue)) ? 'checked' : '';
        return `
            <label class="checkbox ${this.config.readonly ? 'readonly-choices' : ''}">
                <input type="checkbox" name="${this.config.name}" value="${optionValue}" ${checked} />
                ${optionLabel}
            </label>${(this.config.alignment && this.config.alignment === 'vertical') ? '<br>' : ''}
        `;
    }

    /** @override */
    async updateStateOnInput(target) {
        let state = U.stateMultivalue(this.myState.value);
        if (!target.checked) {
            state.delete(target.value);
        } else {
            state.add(target.value);
        }
        this.myState.value = state;
    }

    /**
     * Ensure Set type and apply defaults, but only on initial rendering,
     * when the form has no values yet.
     *
     * @param {State} state
     * @param {Object} config
     * @returns {ComponentState}
     */
    stateHook(state, config) {
        let myState = new ComponentState(state, config.name);
        myState.value = U.stateMultivalue(myState.value);

        if (!state.hasInitialValues && !myState.value.size && "default" in config) {
            myState.value.add(config.default);
        }

        return myState;
    }
}

customElements.define('checklist-component', ChecklistComponent);
