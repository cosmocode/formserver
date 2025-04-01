import {State} from "./State.js";

/**
 * Wraps around the main state object for easier access to the state of a single component
 */
export class ComponentState {

    /** @type {State} the state object */
    state;

    /** @type {string} the name under which this component's data is stored */
    #name;

    /**
     * @param {State} state
     * @param {string} name
     */
    constructor(state, name) {
        this.state = state;
        this.#name = name;
    }

    /**
     * Accessor to the state value of this component
     *
     * @returns {*}
     */
    get value() {
        return this.state.values[this.#name];
    }

    /**
     * Setter for the state of this component
     *
     * @param {*|null} value Setting a null value removes the key from the state
     */
    set value(value) {
        this.state.values[this.#name] = value;
    }

    /**
     * Clear the state value of this component, but keep it in the oldValues
     */
    clear() {
        this.state.oldValues[this.#name] = this.state.values[this.#name];
        this.state.values[this.#name] = null;
    }

    /**
     * Restore the state value of this component from the oldValues
     */
    restore() {
        if(this.value) return; // nothing to restore
        this.state.values[this.#name] = this.state.oldValues[this.#name];
        this.state.oldValues[this.#name] = null;
    }

}
