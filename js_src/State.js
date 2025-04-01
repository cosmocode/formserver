import {deleteProperty, getProperty, setProperty} from "dot-prop";

/**
 * @typedef {Object} StateValueChangeDetail The event detail for a stateValueChangeEvent event
 * @property {string} name The ID of the field that changed
 * @property {string} value The value of the field that changed
 * @todo we may want to add the old value here as well
 */

/**
 * State management class
 */
export class State {

    static STATE_VALUE_CHANGE_EVENT = 'stateValueChangeEvent';

    /** @type {Proxy<object>} The current state values */
    values;

    /** @type {Proxy<object>} Values previously set, before a component was removed */
    oldValues;

    constructor(initialValues = {}) {
        this.values = this.#createProxy(initialValues, State.STATE_VALUE_CHANGE_EVENT);
        this.oldValues = this.#createProxy();
    }

    /**
     * Creates a proxy object for values
     *
     * Handles dot syntax access. Access to non-existing properties will return null.
     *
     * Optionally triggers the given event when a value is set.
     *
     * @param {object} initialValues
     * @param {null|string} withEvent Emit an event with this name when a value is set
     * @returns {Proxy<object>}
     */
    #createProxy(initialValues = {}, withEvent = null) {
        return new Proxy(initialValues, {
                set: (target, name, value) => {
                    if (typeof name !== 'string') {
                        throw new Error('Name must be a string');
                    }

                    if (value === null || value === undefined) {
                        deleteProperty(target, name);
                    } else {
                        setProperty(target, name, value);
                    }

                    if (withEvent) {
                        const event = new CustomEvent(withEvent, {
                            /** @type {StateValueChangeDetail} */
                            detail: {name, value}
                        });
                        document.dispatchEvent(event);
                    }

                    return true;
                },

                get: (target, name) => {
                    if (typeof name !== 'string') {
                        throw new Error('Name must be a string');
                    }

                    return getProperty(target, name, null);
                }
            }
        );
    }
}
