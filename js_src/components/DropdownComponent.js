import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';
import {ComponentState} from "../ComponentState";
import htmlTpl from "html-template-tag";

export class DropdownComponent extends BaseComponent {

    /** @type {[expression.Expression]|Array} Parsed expressions for conditional options */
    optionsExpressions = [];

    initialize(state, fieldConfig) {
        super.initialize(state, fieldConfig);
        this.optionsExpressions = this.getOptionsExpressions();
    }

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

        for (let optionLabel of availableChoices) {
            const option = document.createElement('option');
            optionLabel = String(optionLabel);
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
        // For suffixed fields, the select container needs to expand to fill space
        if (this.config.suffix) {
            selectContainer.classList.add('is-fullwidth');
        }
        selectContainer.appendChild(select);

        control.appendChild(selectContainer);
        field.appendChild(control);

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
     * Evaluate all dependencies, including those attached to options
     *
     * @param {StateValueChangeDetail} detail
     * @returns {boolean}
     */
    shouldUpdate(detail) {
        const superResult = super.shouldUpdate(detail);
        const expressionsResult = U.shouldUpdateFromExpressions(this.optionsExpressions, detail);
        return superResult || expressionsResult;
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

        // without empty_label config, first option is the value
        if (!myState.value && !config.empty_label && !config.multiselect && config.choices) {
            myState.value = config.choices[0];
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
     * Resolves @. references in table contexts before parsing expressions
     * to ensure correct dependency tracking
     *
     * @returns {*[]}
     */
    getOptionsExpressions() {
        const expressions = [];
        const conditionalChoices = this.config.conditional_choices;

        if (!conditionalChoices || !Array.isArray(conditionalChoices)) {
            return expressions;
        }

        const columnContext = this.#getColumnContext();

        for (const choiceSet of conditionalChoices) {
            if (choiceSet.visible) {
                // Resolve @. references for table columns
                const resolvedExpression = U.resolveTableFieldReferences(choiceSet.visible, columnContext);
                const parsed = U.getParsedExpression(resolvedExpression);
                if (parsed) {
                    expressions.push(parsed);
                }
            }
        }

        return expressions;
    }

    /**
     * Extract table column context from field name
     *
     * Finds the last segment matching ._<integer> in the dotted path,
     * supporting both simple ("table._1.field") and nested paths
     * ("page.outer_table._3.inner_table._2.field").
     *
     * @returns {Object|null} {tableName, columnIndex} or null if not in a table
     */
    #getColumnContext() {
        const parts = this.config.name.split('.');
        let colIdx = -1;
        for (let i = parts.length - 1; i >= 0; i--) {
            if (/^_\d+$/.test(parts[i])) {
                colIdx = i;
                break;
            }
        }
        if (colIdx < 1) return null;
        return {
            tableName: parts.slice(0, colIdx).join('.'),
            columnIndex: parts[colIdx]
        };
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
            const columnContext = this.#getColumnContext();

            const filtered = this.config.conditional_choices.filter(set => {
                if (!set.visible) {
                    return true;
                }

                // Resolve @. references for table columns
                const resolvedExpression = U.resolveTableFieldReferences(set.visible, columnContext);
                const expr = U.getParsedExpression(resolvedExpression);

                // If expression failed to parse, show the choices (fail-safe)
                if (!expr) {
                    return true;
                }

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
