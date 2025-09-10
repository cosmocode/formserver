import {deleteProperty, getProperty, setProperty} from "dot-prop";
import {U} from './U.js';

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

    /** @type {boolean} Relevant when populating default values */
    hasInitialValues;

    /** @type {string} */
    formName;

    constructor(formName, initialValues = {}) {
        this.formName = formName;

        this.hasInitialValues = !!Object.keys(initialValues).length;
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
                        State.writeStateToOPFS(this.formName, this)
                            .then(() => {
                                console.log('state updated in OPFS for ' + this.formName);
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

    static async writeStateToOPFS(formName, state) {
        const fileHandle = await this.getOPFSFileHandle(formName);
        // get a writable stream
        const writable = await fileHandle.createWritable();
        // write JSON representation of the state to the stream
        await writable.write(JSON.stringify(U.convertSetsToArray(state)));
        // closing the stream persists contents
        await writable.close();
    }

    static async clearOPFS(formName) {
        const fileHandle = await this.getOPFSFileHandle(formName);
        fileHandle.remove().then(() => {
            console.log("removed state from OPFS");
        });
    }

    static async getValuesFromOPFS(formName) {
        let storedValues = {};
        try {
            const fileHandle = await this.getOPFSFileHandle(formName);
            const file = await fileHandle.getFile();
            const contents = JSON.parse(await file.text());
            return contents.values;
        } catch (e) {
            return storedValues;
        }
    }

    static async getOPFSFileHandle(formName) {
        const opfsRoot = await navigator.storage.getDirectory();
        return await opfsRoot
            .getFileHandle(`${formName}.json`, {create: true});
    }
}
