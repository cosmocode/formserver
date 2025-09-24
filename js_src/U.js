import expression from "expr-eval";
import {setProperty} from "dot-prop";
import html from 'html-template-tag';

/**
 * Utility class
 */
export class U {

    static staticTypes = new Set(["image", "download", "hidden", "hr", "markdown"]);

    /**
     * Return an HTML attribute string formatted as name="value" (default)
     * or name=value for use in html template tag
     *
     * @param {string} name
     * @param {string|null} value
     * @param {boolean} specialChars
     * @returns {string}
     */
    static attrHTML(name, value, specialChars = true) {
        if (value && specialChars) {
            value = '"' + value + '"';
        }
        return value ? `${name}=${value}` : '';
    }

    /**
     * Creates field with label.
     * Components will create, fill and attach control to the field element.
     *
     * @param {Object} fieldConfig
     * @param {Array|null} additionalClasses Optional additional CSS classes for field element
     * @param {boolean|null} labelIsDiv Render label content in <div> rather than <label>
     * @param {boolean|null} suppressLabel Suppress label
     * @returns {HTMLDivElement}
     */
    static createField(fieldConfig, additionalClasses = [], labelIsDiv = false, suppressLabel = false) {
        const field = document.createElement("div");
        field.classList.add("field", ...additionalClasses);

        if (fieldConfig.label && !suppressLabel) {
            const labelTag = labelIsDiv ? "div" : "label";
            const label = document.createElement(labelTag);
            label.classList.add("label");
            if (fieldConfig.labelsmall) {
                label.classList.add("label-smaller");
            }
            label.innerText = html`${fieldConfig.label}` + this.requiredMark(fieldConfig);

            field.appendChild(label);

            field.insertAdjacentHTML('beforeend', this.tooltipHint(fieldConfig));
            if (fieldConfig.modal) {
                field.appendChild(this.modalHint(fieldConfig));
                field.appendChild(this.modal(fieldConfig));
            }
        }

        return field;
    }

    /**
     * Adds visual mark for required form fields
     *
     * @param {object} config
     * @returns {string}
     */
    static requiredMark(config) {
        const isStaticType = this.staticTypes.has(config.type);
        return (!isStaticType && (!config.validation || config.validation.required !== false)) ? ' *' : '';
    }

    /**
     * Creates tooltip HTML
     *
     * @param {Object} config
     * @returns {string}
     */
    static tooltipHint(config) {
        if (!config.tooltip) {
            return '';
        }

        return `<span class="tooltip tooltip-hint" data-tooltip="${config.tooltip}">!</span>`;
    }

    /**
     * Creates modal hint button
     *
     * @param {Object} config
     * @returns {HTMLElement|null}
     */
    static modalHint(config) {
        if (!config.modal) {
            return null;
        }

        const hint = document.createElement("span");
        hint.innerText = "?";
        hint.classList.add("modal-hint");
        hint.addEventListener('click', this.modalEventHandler);

        return hint;
    }

    /**
     * Modals are triggered via modalHint and in image preview in UploadComponent
     * @param {Event} e
     */
    static modalEventHandler(e) {
        e.preventDefault();
        const modal = e.target.nextElementSibling;
        modal.classList.add('is-active');
        document.querySelector('html').classList.add('is-clipped');
    }

    /**
     * Creates modal element with event handlers.
     * Must be after modalHint in the DOM
     *
     * @param {Object} config
     * @returns {null|HTMLDivElement}
     */
    static modal(config) {
        if (!config.modal) {
            return null;
        }

        const modal = document.createElement("div");
        modal.classList.add("modal");

        const backdrop = document.createElement("div");
        backdrop.classList.add("modal-background");
        backdrop.addEventListener("click", (e) => {
            this.#closeModal(e, modal);
        });

        const contentContainer = document.createElement("div");
        contentContainer.classList.add("modal-content");

        const content = document.createElement("div");
        content.classList.add("box", "content");
        content.innerHTML = config.modal;

        contentContainer.appendChild(content);

        const button = document.createElement("button");
        button.type = "button";
        button.classList.add("modal-close", "is-large");
        button.addEventListener("click", (e) => {
            this.#closeModal(e, modal);
        });

        modal.appendChild(backdrop);
        modal.appendChild(contentContainer);
        modal.appendChild(button);

        return modal;
    }

    /**
     *
     * @param {Event} e
     * @param {HTMLElement} modal
     */
    static #closeModal(e, modal) {
        e.preventDefault();
        modal.classList.remove("is-active");
        document.querySelector('html').classList.remove('is-clipped');
    }

    /**
     * Attaches and renders children of the passed element
     *
     * @param {HTMLElement} parentElement
     * @param {object} childrenConfig
     * @param {State} state
     */
    static attachChildren(parentElement, childrenConfig, state) {
        const parentName = parentElement.name ? parentElement.name + '.' : '';

        // an additional wrapper is needed for Bulma columns
        const columnWrapper = document.createElement('div');
        columnWrapper.classList.add('columns', 'is-multiline');
        parentElement.appendChild(columnWrapper);

        Object.keys(childrenConfig).forEach(key => {
            const fieldConfig = childrenConfig[key];
            fieldConfig.name = `${parentName}${key}`; // add full dotted field ID to config
            const element = document.createElement(`${fieldConfig.type}-component`);
            element.initialize(state, fieldConfig);

            columnWrapper.appendChild(element);
            element.render(); // initial rendering
        });
    }

    /**
     * Attaches and renders children of the table parent
     *
     * @param {HTMLElement} parentElement
     * @param {object} childrenConfig
     * @param {State} state
     * @param {number} repeat
     */
    static attachTableRows(parentElement, childrenConfig, state, repeat) {
        const parentName = parentElement.name ? parentElement.name + '.' : '';

        Object.keys(childrenConfig).forEach(key => {
            const fieldConfig = childrenConfig[key];

            const rowWrapper = document.createElement('tr');

            // check if the row should be hidden
            const visibilityExpression = U.getParsedExpression(fieldConfig.visible);
            const visible = !visibilityExpression || U.conditionMatches(visibilityExpression, state);
            // can't use early return because children elements must always exist in order to update their state
            if (!visible) {
                rowWrapper.classList.add("hidden");
            }

            const labelTh = document.createElement('th');
            labelTh.insertAdjacentText("afterbegin", fieldConfig.label + this.requiredMark(fieldConfig));
            labelTh.insertAdjacentHTML("beforeend", U.tooltipHint(fieldConfig));
            if (fieldConfig.modal) {
                labelTh.appendChild(U.modalHint(fieldConfig));
                labelTh.appendChild(U.modal(fieldConfig));
            }
            rowWrapper.appendChild(labelTh);

            for (let i = 0; i < repeat; i++) {
                const idx = `${i + 1}`;
                fieldConfig.name = `${parentName}${idx}.${key}`; // add full dotted field ID to config
                const element = document.createElement(`${fieldConfig.type}-component`);
                element.initialize(state, fieldConfig);

                const fieldTd = document.createElement('td');
                fieldTd.appendChild(element);

                rowWrapper.appendChild(fieldTd);
                element.render(); // initial rendering
            }

            parentElement.appendChild(rowWrapper);
        });
    }

    /**
     * Load field definitions from formconfig
     * @returns {Object}
     */
    static loadFormConfig() {
        return formconfig.form;
    }

    /**
     * Returns the "values" object from form config supplied by backend
     * @returns {Object}
     */
    static loadFormValues() {
        return formconfig.values || {};
    }

    /**
     * Returns value of a given meta key, or null it the key doesn't exist.
     *
     * @param {string} key
     * @returns {*|null}
     */
    static formMeta(key) {
        if (Object.hasOwn(formconfig.meta, key)) {
            return formconfig.meta[key];
        }
        return null;
    }

    /**
     * Returns a string from the i18l dictionary provided by backend via formconfig
     *
     * @param {string} key
     * @returns {string|null}
     */
    static getLang(key) {
        return formconfig.lang[key] || null;
    }

    /**
     * @param {string|null} visible
     * @returns {expression.Expression}
     */
    static getParsedExpression(visible) {
        if (!visible) {
            return null;
        }
        try {
            const parser = new expression.Parser({
                operators: {
                    logical: true,
                    comparison: true,
                    'in': true,
                }
            });

            // overwrite "in" to handle comparisons with sets as well as strings
            parser.binaryOps.in = (left, right) => {
                const a = new Set(typeof left === 'string' ? new Array(left) : left);
                const b = new Set(right);
                return !!a.intersection(b).size;
            };

            return parser.parse(visible);
        } catch (e) {
            console.error(`Error parsing visibility expression ${visible}`, e);
        }

    }

    /**
     * Evaluate expression against current form state
     *
     * @param {expression.Expression} expression
     * @param {Object} state
     * @returns {boolean}
     */
    static conditionMatches(expression, state) {
        try {
            const vars = expression.variables({withMembers: true});

            // create an object with the variables and their values
            // initializes non-existing variables with null
            const data = {};
            vars.forEach(v => {
                setProperty(data, v, state.values[v]);
            });

            return !!expression.evaluate(data);
        } catch (e) {
            console.error('Error evaluating visibility expression', e);
            return true; // on error, show the element
        }

    }

    /**
     * Takes a nested object structure and converts any found sets into arrays.
     * Needed to post JSON data to backend, otherwise set entries are lost.
     *
     * @param {Object} data
     * @returns {{}|[]|*}
     */
    static convertSetsToArray(data) {
        if (data instanceof Set) {
            return Array.from(data);
        } else if (typeof data === 'object' && data !== null) {
            if (Array.isArray(data)) {
                return data.map(item => this.convertSetsToArray(item));
            } else {
                const newObject = {};
                for (const key in data) {
                    if (data.hasOwnProperty(key)) {
                        newObject[key] = this.convertSetsToArray(data[key]);
                    }
                }
                return newObject;
            }
        }
        return data; // Return primitive values as is
    }

    /**
     * Enforces state of multivalue components to s Set object
     *
     * @param {*} value
     * @returns {Set}
     */
    static stateMultivalue(value) {
        if (value instanceof Set) {
            return value;
        }
        if (Array.isArray(value)) {
            return new Set(value);
        }
        return new Set();
    }

    /**
     * Collects expressions from subitems of a configuration object
     *
     * @param {Object} config - Configuration object containing subitems
     * @param {string} itemsKey - Key to access the subitems array (e.g., 'conditional_choices', 'children')
     * @returns {Array} Array of parsed expressions
     */
    static getSubitemsExpressions(config, itemsKey) {
        const expressionConfigKey = "visible";
        const expressions = [];
        const items = config[itemsKey];

        if (!items) {
            return expressions;
        }

        // Handle array of items (like conditional_choices)
        if (Array.isArray(items)) {
            for (const item of items) {
                if (item[expressionConfigKey]) {
                    expressions.push(this.getParsedExpression(item[expressionConfigKey]));
                }
            }
        }
        // Handle object with keys (like children)
        else if (typeof items === 'object') {
            for (const itemKey in items) {
                const item = items[itemKey];
                if (item[expressionConfigKey]) {
                    expressions.push(this.getParsedExpression(item[expressionConfigKey]));
                }
            }
        }

        return expressions.filter((expr) => { return expr; });
    }

    /**
     * Checks if expressions should trigger an update based on state change
     *
     * @param {Array} expressions - Array of parsed expressions
     * @param {StateValueChangeDetail} detail - State change detail
     * @returns {boolean} True if any expression depends on the changed state
     */
    static shouldUpdateFromExpressions(expressions, detail) {
        if (!expressions.length) {
            return false;
        }

        const vars = [];
        for (const expr of expressions) {
            vars.push(...expr.variables({withMembers: true}));
        }

        return vars.includes(detail.name);
    }
}
