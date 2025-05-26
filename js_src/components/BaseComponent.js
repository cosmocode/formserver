import morphdom from 'morphdom';
import {ComponentState} from "../ComponentState";
import {State} from "../State";
import {ValidatorError} from "../ValidatorError";
import {U} from "../U";

/**
 * Base class for all form components
 */
export class BaseComponent extends HTMLElement {
    /** @type {boolean} component has been initialized */
    #isInitialized = false;
    /** @type {ComponentState} The current state */
    myState;
    /** @type {object} The configuration for this component as defined in the main YAML/JSON */
    config;
    /** @type {string} The name of the field in the form data */
    name;
    /** @type {string|null} The current validation error message */
    validationError = null;
    /** @type {boolean} Is the component curently visible? */
    visible = true;

    /** @type {Array<function>} Validators for the component */
    validators = [];

    /** @type {expression.Expression|null} The parsed visibility expression */
    #visibilityExpression = null;

    // region lifecycle management

    /**
     * Constructor
     */
    constructor() {
        if (new.target === BaseComponent) {
            throw new TypeError('BaseComponent is abstract and cannot be instantiated directly');
        }
        super();

        // necessary for unregistering event handler
        // see https://stackoverflow.com/a/33386309
        this.onStateValueChange = this.onStateValueChange.bind(this);
    }

    /**
     * Initialize the component with the form data and field configuration
     *
     * Called from FormComponent before the component is added to the DOM
     *
     * @param {State} state The current state
     * @param {object} fieldConfig The configuration for this component as defined in the main YAML/JSON
     */
    initialize(state, fieldConfig) {
        this.myState = this.stateHook(state, fieldConfig);
        this.config = fieldConfig;
        this.name = fieldConfig.name;
        this.classList.add('component');

        if (this.config.visible) {
            this.#visibilityExpression = U.getParsedExpression(this.config.visible);
        }

        this.#isInitialized = true;
    }

    /**
     * Components can overwrite this to influence their own initial state.
     * Relevant when value type in not represented in JSON (e.g. a Set object).
     *
     * @param {State} state
     * @param {object} config Field config
     * @returns {ComponentState}
     */
    stateHook(state, config) {
        return new ComponentState(state, config.name);
    }

    /**
     * Called when the component is added to the DOM
     *
     * Registers event listener for input events and state changes
     */
    connectedCallback() {
        if (!this.#isInitialized) {
            throw new Error('Component was not initialized before adding to DOM');
        }

        // make the component rerender on state changes
        document.addEventListener(State.STATE_VALUE_CHANGE_EVENT, this.onStateValueChange);

        // register the default state change handler
        this.registerStateChangeHandler();
    }

    /**
     * Called when the component is removed from the DOM
     *
     * Unregisters the event listener for state changes
     */
    disconnectedCallback() {
        document.removeEventListener(State.STATE_VALUE_CHANGE_EVENT, this.onStateValueChange);
    }

    // endregion

    // region rendering

    /**
     * Render the component
     *
     * The default implementation uses morphdom to update the component from the HTML returned by the html() method
     */
    render() {
        this.handleConditionalVisibility();

        // Empty and hide the component if it should not be visible
        let html;
        if (this.visible) {
            this.restoreState(); // restore the state if the component is visible
            html = this.htmlWrap(this.html());
            this.style.display = '';
        } else {
            this.myState.clear(); // clear the state if the component is not visible
            html = null;
            this.style.display = 'none';
        }

        // we do not want the component be replaced by the HTML but its children.
        // Morphdom's childrenOnly takes care of that, but this means we also need to wrap the new HTML in
        // an element whose children will be used to replace the children of the component
        const disposableWrapper = document.createElement('div');
        if (html instanceof HTMLElement) {
            disposableWrapper.appendChild(html);
        }

        morphdom(this, disposableWrapper, {childrenOnly: true});
    }

    /**
     * @return {HTMLElement|string} The HTML representation of the component
     */
    html() {
        const el = document.createElement('div');
        el.innerText = 'Not implemented';
        return el;
    }

    /**
     * Wrap the given HTML in a div and apply Bulma and Error handling
     *
     * @param {HTMLElement|string} html
     * @returns {HTMLDivElement}
     */
    htmlWrap(html) {
        const wrapper = document.createElement('div');
        const classes = this.config.column ?? "is-full";
        wrapper.classList.add('column',  ...classes.split(/\s+/));

        if (typeof html === 'string') {
            wrapper.innerHTML = html;
        } else {
            wrapper.appendChild(html);
        }

        if (this.validationError) {
            wrapper.classList.add('has-errors');
            let errorElement = wrapper.querySelector('.help.is-danger');
            if (!errorElement) {
                errorElement = document.createElement('p');
                errorElement.classList.add('help', 'is-danger');
                wrapper.appendChild(errorElement);
            }
            errorElement.innerText = this.validationError;
        }

        return wrapper;
    }


    /**
     * Restore the state of the component from an old state
     *
     * The default implementations should be fine for simple input components.
     * Fields with variable data sets will want to check the old state against the current data set
     * or simply not restore the state.
     */
    restoreState() {
        this.myState.restore();
    }

    /**
     * Checks if the component should be visible
     *
     * This uses the visible property of the component configuration and evaluates it as an expression.
     * The default render method uses this to set the display style of the component.
     *
     */
    handleConditionalVisibility() {
        if (!this.#visibilityExpression) return;

        this.visible = U.conditionMatches(this.#visibilityExpression, this.myState.state);
    }

    // endregion

    // region validation

    /**
     * Execute validations on the component's state
     *
     * @throws {ValidatorError} If a validation fails
     */
    executeValidators() {
        if (!this.visible) {
            return;
        }

        if (
            (!this.config.validation || this.config.validation.required !== false) &&
            (this.myState.value === null || this.myState.value.length === 0 || this.myState.value.size === 0)
        ) {
            throw new ValidatorError(this.name, U.getLang("error_required"));
        }
    }

    /**
     * Validate the component and rerender it
     *
     * @returns {boolean}
     */
    validate() {
        try {
            this.executeValidators();
        } catch (e) {
            if (e instanceof ValidatorError) {
                this.validationError = e.message;
                this.render();
                return false;
            }
            throw e;
        }

        if (this.validationError) {
            this.validationError = null;
            this.render();
        }

        return true;
    }

    // endregion

    // region event handling

    /**
     * Register a handler to update the state if the component is changed by the user
     *
     * Default implementation listens for input events and calls updateStateOnInput
     */
    registerStateChangeHandler() {
        this.addEventListener('input', (e) => {
            this.updateStateOnInput(e.target);
            e.stopPropagation();
        });
    }

    /**
     * Default handler for the stateValueChanged event
     *
     * Should not be overwritten. Instead, implement shouldUpdate() to influence the update behavior
     *
     * @param {CustomEvent} e The stateValueChanged event
     */
    onStateValueChange(e) {
        if (this.shouldUpdate(e.detail)) {
            this.render();
        }
    }

    /**
     * Default implementation for updating the state of the component
     *
     * This is method is registered in registerStateChangeHandler() if that method has not been overwritten
     *
     * IMPORTANT: state changes should always be atomic, i.e. the state should be updated in one go.
     *
     * @param {HTMLInputElement|HTMLTextAreaElement} target
     */
    async updateStateOnInput(target) {
        this.myState.value = target.value;
    }

    /**
     * Should the component update for this change?
     *
     * Default implementation will only update when the field that changed is a dependency used in
     * checking visibility.
     *
     * @param {StateValueChangeDetail} detail The details passed in the StateValueChangeEvent event
     * @returns {boolean}
     */
    shouldUpdate(detail) {
        if (!this.#visibilityExpression) return false;
        const vars = this.#visibilityExpression.variables({withMembers: true});
        return vars.includes(detail.name);
    }

    // endregion
}
