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

    /** @type {Object} The current state values */
    values;

    /** @type {Object} Values previously set, before a component was removed */
    oldValues;

    /** @type {boolean} Relevant when populating default values */
    hasInitialValues;

    /** @type {string} */
    formName;

    /** @type {function|null} */
    #onStateInitializedCallback = null;

    /**
     * Creates a new State instance
     *
     * @param {string} formName Unique name for the form (used for OPFS storage)
     * @param {object} initialValues Initial values to populate the state with (if no OPFS values exist)
     * @param {function} onStateInitialized Callback when state is initialized
     */
    constructor(formName, initialValues = {}, onStateInitialized = null) {
        this.formName = formName;
        this.#onStateInitializedCallback = onStateInitialized;
        // noinspection JSIgnoredPromiseFromCall
        this.#initialize(initialValues);
    }

    /**
     * Initialize the state object
     *
     * Prefers OPFS stored values over initialValues passed to the constructor.
     *
     * Calls the onStateInitialized callback when done.
     *
     * @param {object} initialValues
     * @returns {Promise<void>}
     */
    async #initialize(initialValues = {}) {
        // load stored values from OPFS
        let valuesToUse = await this.getValuesFromOPFS();

        if((!Object.keys(valuesToUse).length)) {
            valuesToUse = initialValues;
        }

        this.hasInitialValues = !!Object.keys(valuesToUse).length;
        this.values = this.#createProxy(valuesToUse, State.STATE_VALUE_CHANGE_EVENT);
        this.oldValues = this.#createProxy();

        if (this.#onStateInitializedCallback) {
            this.#onStateInitializedCallback();
        }
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
     * @returns {Object}
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
                        // noinspection JSIgnoredPromiseFromCall
                        this.writeStateToOPFS();

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

    async writeStateToOPFS() {
        const fileHandle = await this.getOPFSFileHandle();
        // get a writable stream
        const writable = await fileHandle.createWritable();
        // write JSON representation of the state to the stream
        await writable.write(JSON.stringify(U.convertSetsToArray(this)));
        // closing the stream persists contents
        await writable.close();
    }

    async clearOPFS() {
        const fileHandle = await this.getOPFSFileHandle();
        fileHandle.remove().then(() => {
            console.log("removed state from OPFS");
        });
    }

    async getValuesFromOPFS() {
        let storedValues = {};
        try {
            const fileHandle = await this.getOPFSFileHandle();
            const file = await fileHandle.getFile();
            const contents = JSON.parse(await file.text());
            return contents.values;
        } catch (e) {
            return storedValues;
        }
    }

    async getOPFSFileHandle() {
        const opfsRoot = await navigator.storage.getDirectory();
        return await opfsRoot
            .getFileHandle(`${this.formName}.json`, {create: true});
    }
}
