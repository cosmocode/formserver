import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';
import {ComponentState} from "../ComponentState";

export class DropdownComponent extends BaseComponent {

    /** @type {[expression.Expression]|Array} Parsed expressions for conditional options */
    optionsExpressions = [];

    initialize(state, fieldConfig) {
        super.initialize(state, fieldConfig);
        this.optionsExpressions = this.getOptionsExpressions();
    }

    html() {
        const select = document.createElement('select');
        select.name = this.config.name;
        if (this.config.multiselect) {
            select.multiple = true;
        }
        if (this.config.size) {
            select.size = this.config.size;
        }

        if (this.config.readonly) {
            select.classList.add('readonly-select');
        }

        if (!this.config.multiselect && this.config.empty_label) {
            const option = document.createElement('option');
            option.value = '';
            option.innerText = this.config.empty_label;
            select.appendChild(option);
        }

        const availableChoices = this.#getAvailableChoices();

        for (const optionLabel of availableChoices) {
            const option = document.createElement('option');
            option.value = optionLabel;
            option.innerText = optionLabel;
            option.selected = select.multiple ? (this.myState.value && this.myState.value.has(optionLabel)) : (this.myState.value && this.myState.value === optionLabel);
            select.appendChild(option);
        }

        const selectContainer = document.createElement('div');
        selectContainer.classList.add('select');
        if (this.config.multiselect) {
            selectContainer.classList.add('is-multiple');
        }
        selectContainer.appendChild(select);

        const control = document.createElement('div');
        control.classList.add('control');

        control.appendChild(selectContainer);

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

        field.appendChild(control);

        return field;
    }

    /** @override */
    async updateStateOnInput(target) {
        // null state when empty / placeholder option has been selected
        if (target.value === '') {
            this.myState.value = null;
            return;
        }

        // no need to override further in single-select field
        if (!this.config.multiselect) {
            return super.updateStateOnInput(target);
        }

        let state = U.stateMultivalue(this.myState.value);

        if (state.has(target.value)) {
            state.delete(target.value);
        } else {
            state.add(target.value);
        }
        this.myState.value = state;
    }

    /**
     * Evaluate all dependencies of options
     *
     * @param {StateValueChangeDetail} detail
     * @returns {boolean}
     */
    shouldUpdate(detail) {

        const optionsShouldUpdate = () => {
            if (!this.config.conditional_choices || !this.optionsExpressions.length) {
                return false;
            }

            const vars = [];
            for (const expr of this.optionsExpressions) {
                vars.push(... expr.variables({withMembers: true}));
            }

            return vars.includes(detail.name);
        }

        // check field visibility condition and options visibility conditions
        return super.shouldUpdate(detail) || optionsShouldUpdate();
    }

    /**
     * Ensure Set type for multiselect values.
     * Apply defaults, but only on initial rendering, when the form has no values yet.
     *
     * @param {State} state
     * @param {Object} config
     * @returns {ComponentState}
     */
    stateHook(state, config) {
        let myState = new ComponentState(state, config.name);

        if (!state.hasInitialValues && !myState.value && "default" in config) {
            myState.value = config.default;
        }

        if (config.multiselect) {
            myState.value = U.stateMultivalue(myState.value);

            if (!state.hasInitialValues && !myState.value.size && "default" in config) {
                myState.value.add(config.default);
            }
        }

        return myState;
    }

    /**
     * Clear state when options change to remove potentially invalid choices
     *
     * @override
     */
    restoreState() {
        if (!this.myState.state.oldValues[this.name]) return;
        this.myState.clear();
    }

    /**
     * Collects conditional expressions attached to options,
     * which will be evaluated in shouldUpdate()
     *
     * @returns {*[]}
     */
    getOptionsExpressions() {
        const expressions= [];
        if (!this.config.conditional_choices) {
            return expressions;
        }

        for (const set of this.config.conditional_choices) {
            expressions.push(U.getParsedExpression(set.visible));
        }

        return expressions.filter((expr) => { return expr; });
    }

    /**
     * Get choices that satisfy defined visibility conditions
     *
     * @returns {Set}
     */
    #getAvailableChoices() {
        if (this.config.choices) {
            return new Set(this.config.choices);
        }

        if (this.config.conditional_choices) {
            const filtered = this.config.conditional_choices.filter(set => {
                if (!set.visible) {
                    return true;
                }
                const expr = U.getParsedExpression(set.visible);
                return U.conditionMatches(expr, this.myState.state);
            });

            const flattened = filtered.map((ch) => {
                return ch.choices
            }).flat();

            // remove duplicates
            return new Set(flattened);
        }

        return new Set();
    }

}

customElements.define('dropdown-component', DropdownComponent);
