import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';
import {ValidatorError} from "../ValidatorError";

/**
 * Sorter component for drag-and-drop sorting of items
 */
export class SorterComponent extends BaseComponent {

    /**
     * Renders the HTML for the sorter component
     * @returns {HTMLElement} The rendered field element
     */
    html() {
        const field = U.createField(this.config);

        const control = document.createElement("div");
        control.classList.add("control");

        const sortableList = document.createElement("ul");
        sortableList.classList.add("sortable-list");
        sortableList.setAttribute("draggable", "false");

        // add flex classes if alignment is horizontal
        if (this.config.alignment === 'horizontal') {
            sortableList.classList.add("is-flex", "is-flex-wrap-wrap", "sortable-list-horizontal");
        }

        // get items from state or config
        const items = this.getItems();

        items.forEach((item, index) => {
            const listItem = document.createElement("li");
            const isEnabled = item.enabled !== false;

            listItem.classList.add("sortable-item", "box", "is-flex", "is-align-items-center", "p-3", "m-2");
            if (!isEnabled) {
                listItem.classList.add("is-disabled");
                listItem.style.opacity = "0.5";
            }

            listItem.setAttribute("draggable", isEnabled ? "true" : "false");
            listItem.setAttribute("data-value", item.value || item);
            listItem.setAttribute("data-enabled", isEnabled.toString());
            listItem.style.cursor = isEnabled ? "grab" : "default";

            listItem.innerHTML = `
                <span class="drag-handle icon is-small mr-2" style="cursor: ${isEnabled ? 'grab' : 'not-allowed'};">
                    ${this.#getDragIcon(isEnabled)}
                </span>
                <label class="checkbox mr-2" style="cursor: pointer;">
                    <input type="checkbox" ${isEnabled ? 'checked' : ''} data-toggle-item="${item.value || item}">
                </label>
                <span class="item-label ${isEnabled ? '' : 'has-text-grey-light'}">${item.label || item}</span>
            `;
            sortableList.appendChild(listItem);
        });

        control.appendChild(sortableList);
        field.appendChild(control);

        return field;
    }

    /**
     * Updates the component state based on the current order and enabled state of items
     */
    updateStateFromSortedList() {
        const sortableList = this.querySelector('.sortable-list');
        if (!sortableList) return;

        const items = [...sortableList.querySelectorAll('.sortable-item')];

        this.myState.value = items.map(item => ({
            value: item.getAttribute('data-value'),
            enabled: item.getAttribute('data-enabled') === 'true'
        }));
    }

    /**
     * Gets the items to display, merging state and config data
     * @returns {Array} Array of item objects with value, label, and enabled properties
     */
    getItems() {
        const stateItems = this.myState.value;
        const configItems = this.config.items || [];

        // if we have state data with enabled/disabled info, use it
        if (stateItems && Array.isArray(stateItems) && stateItems.length > 0 &&
            typeof stateItems[0] === 'object' && 'enabled' in stateItems[0]) {
            return stateItems.map(stateItem => {
                const configItem = configItems.find(ci => (ci.value || ci) === stateItem.value);
                return {
                    value: stateItem.value,
                    label: configItem ? (configItem.label || configItem) : stateItem.value,
                    enabled: stateItem.enabled
                };
            });
        }

        // if we have simple array state (just values), merge with config
        if (stateItems && Array.isArray(stateItems)) {
            return stateItems.map(value => {
                const configItem = configItems.find(ci => (ci.value || ci) === value);
                return {
                    value: value,
                    label: configItem ? (configItem.label || configItem) : value,
                    enabled: true // default to enabled
                };
            });
        }

        // fall back to config items, all enabled by default
        return configItems.map(item => ({
            value: item.value || item,
            label: item.label || item,
            enabled: true
        }));
    }

    /**
     * Generates the drag handle icon SVG
     * @param {boolean} isEnabled Whether the item is enabled
     * @returns {string} SVG markup for the drag icon
     */
    #getDragIcon(isEnabled) {
        return `<svg width="12" height="16" viewBox="0 0 12 16" xmlns="http://www.w3.org/2000/svg">
            <circle cx="3" cy="3" r="1.5" fill="${isEnabled ? '#999' : '#ccc'}"/>
            <circle cx="9" cy="3" r="1.5" fill="${isEnabled ? '#999' : '#ccc'}"/>
            <circle cx="3" cy="8" r="1.5" fill="${isEnabled ? '#999' : '#ccc'}"/>
            <circle cx="9" cy="8" r="1.5" fill="${isEnabled ? '#999' : '#ccc'}"/>
            <circle cx="3" cy="13" r="1.5" fill="${isEnabled ? '#999' : '#ccc'}"/>
            <circle cx="9" cy="13" r="1.5" fill="${isEnabled ? '#999' : '#ccc'}"/>
        </svg>`;
    }

    // region drag and drop event handling

    /**
     * Override to handle drag and drop events and checkbox toggles
     */
    registerStateChangeHandler() {
        this.addEventListener('dragstart', this.handleDragStart.bind(this));
        this.addEventListener('dragover', this.handleDragOver.bind(this));
        this.addEventListener('drop', this.handleDrop.bind(this));
        this.addEventListener('dragend', this.handleDragEnd.bind(this));
        this.addEventListener('change', this.handleToggleChange.bind(this));
    }

    /**
     * Handles the start of a drag operation
     * @param {DragEvent} e The drag start event
     */
    handleDragStart(e) {
        if (!e.target.classList.contains('sortable-item')) return;
        if (e.target.getAttribute('data-enabled') === 'false') {
            e.preventDefault();
            return;
        }

        e.target.classList.add('dragging');
        e.target.style.cursor = "grabbing";
        e.target.style.opacity = "0.5";
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', e.target.outerHTML);
        e.dataTransfer.setData('text/plain', e.target.getAttribute('data-value'));
    }

    /**
     * Handles drag over events to enable dropping
     * @param {DragEvent} e The drag over event
     */
    handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';

        const sortableList = e.target.closest('.sortable-list');
        if (!sortableList) return;

        const isHorizontal = sortableList.classList.contains('sortable-list-horizontal');
        const afterElement = this.getDragAfterElement(sortableList, isHorizontal ? e.clientX : e.clientY, isHorizontal);
        const dragging = sortableList.querySelector('.dragging');

        if (afterElement == null) {
            sortableList.appendChild(dragging);
        } else {
            sortableList.insertBefore(dragging, afterElement);
        }
    }

    /**
     * Handles the drop event and updates state
     * @param {DragEvent} e The drop event
     */
    handleDrop(e) {
        e.preventDefault();
        this.updateStateFromSortedList();
    }

    /**
     * Handles the end of a drag operation and resets visual state
     * @param {DragEvent} e The drag end event
     */
    handleDragEnd(e) {
        if (e.target.classList.contains('sortable-item')) {
            e.target.classList.remove('dragging');
            const isEnabled = e.target.getAttribute('data-enabled') === 'true';
            e.target.style.cursor = isEnabled ? "grab" : "default";
            e.target.style.opacity = isEnabled ? "1" : "0.5";
        }
    }

    /**
     * Determines which element should come after the dragged element based on position
     * @param {HTMLElement} container The container element
     * @param {number} position The mouse position (x or y coordinate)
     * @param {boolean} isHorizontal Whether the layout is horizontal
     * @returns {HTMLElement|null} The element that should come after the dragged element
     */
    getDragAfterElement(container, position, isHorizontal = false) {
        const draggableElements = [...container.querySelectorAll('.sortable-item:not(.dragging)')];

        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            let offset;

            if (isHorizontal) {
                offset = position - box.left - box.width / 2;
            } else {
                offset = position - box.top - box.height / 2;
            }

            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    /**
     * Handles checkbox toggle events to enable/disable items
     * @param {Event} e The change event
     */
    handleToggleChange(e) {
        if (!e.target.matches('input[type="checkbox"][data-toggle-item]')) return;

        const itemValue = e.target.getAttribute('data-toggle-item');
        const isEnabled = e.target.checked;
        const listItem = e.target.closest('.sortable-item');

        // update visual state
        listItem.setAttribute('data-enabled', isEnabled);
        listItem.setAttribute('draggable', isEnabled);
        listItem.style.cursor = isEnabled ? "grab" : "default";
        listItem.style.opacity = isEnabled ? "1" : "0.5";

        if (isEnabled) {
            listItem.classList.remove('is-disabled');
        } else {
            listItem.classList.add('is-disabled');
        }

        // update drag handle and label styling
        const dragHandle = listItem.querySelector('.drag-handle svg');
        const circles = dragHandle.querySelectorAll('circle');
        circles.forEach(circle => {
            circle.setAttribute('fill', isEnabled ? '#999' : '#ccc');
        });

        const label = listItem.querySelector('.item-label');
        if (isEnabled) {
            label.classList.remove('has-text-grey-light');
        } else {
            label.classList.add('has-text-grey-light');
        }

        this.updateStateFromSortedList();
    }

    // endregion

    /**
     * Override the base validation to handle our complex state structure
     */
    executeValidators() {
        if (!this.visible) {
            return;
        }

        const hasEnabledItems = this.myState.value &&
            Array.isArray(this.myState.value) &&
            this.myState.value.some(item => item.enabled !== false);

        if (
            (!this.config.validation || this.config.validation.required !== false) &&
            !hasEnabledItems
        ) {
            throw new ValidatorError(this.name, U.getLang("error_required"));
        }
    }
}

customElements.define('sorter-component', SorterComponent);
