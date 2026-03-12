import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';
import {ValidatorError} from "../ValidatorError";

/**
 * Sorter component for drag-and-drop sorting of items
 */
export class SorterComponent extends BaseComponent {

    initialize(state, fieldConfig) {
        super.initialize(state, fieldConfig);

        const normalizedItems = this.#normalizeStateItems();
        if (normalizedItems) {
            this.myState.value = normalizedItems;
        }
    }

    /**
     * Renders the HTML for the sorter component
     * @returns {HTMLElement} The rendered field element
     */
    html() {
        const field = U.createField(this.config);
        const hasCheckboxes = this.#hasCheckboxes();
        this.dataset.hasCheckboxes = hasCheckboxes ? 'true' : 'false';

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
            const isFixed = (index === 0 && this.config.fixFirst) ||
                            (index === items.length - 1 && this.config.fixLast);
            const isEnabled = isFixed ? true : (hasCheckboxes ? item.enabled !== false : true);

            listItem.classList.add("sortable-item", "box", "is-flex", "is-align-items-center", "p-3", "m-2");
            if (!isEnabled) {
                listItem.classList.add("is-disabled");
                listItem.style.opacity = "0.5";
            }

            listItem.setAttribute("draggable", (isEnabled && !isFixed) ? "true" : "false");
            listItem.setAttribute("data-value", item.value || item);
            listItem.setAttribute("data-enabled", isEnabled.toString());
            if (isFixed) {
                listItem.setAttribute("data-fixed", "true");
            }
            listItem.style.cursor = (isEnabled && !isFixed) ? "grab" : "default";

            const showCheckbox = hasCheckboxes && !isFixed;
            const checkboxHtml = showCheckbox ? `
                <label class="checkbox mr-2" style="cursor: pointer;">
                    <input type="checkbox" ${isEnabled ? 'checked' : ''} data-toggle-item="${item.value || item}">
                </label>` : '';

            const showDragHandle = !isFixed;
            listItem.innerHTML = `
                ${showDragHandle ? `<span class="drag-handle icon is-small mr-2" style="cursor: ${isEnabled ? 'grab' : 'not-allowed'};">
                    ${this.#getDragIcon(isEnabled)}
                </span>` : ''}
                ${checkboxHtml}
                <span class="item-label ${isEnabled ? '' : 'has-text-dark'}">${item.value || item}</span>
            `;
            sortableList.appendChild(listItem);
        });

        control.appendChild(sortableList);
        field.appendChild(control);

        return field;
    }

    /**
     * Updates the component state based on the current order and enabled state of items.
     * We store values in Arrays, different from Checklists and Dropdowns, because
     * here order is the priority, not uniqueness.
     */
    updateStateFromSortedList() {
        const sortableList = this.querySelector('.sortable-list');
        if (!sortableList) return;

        const items = [...sortableList.querySelectorAll('.sortable-item')];

        const hasCheckboxes = this.#hasCheckboxes();
        this.myState.value = items.map(item => ({
            value: item.getAttribute('data-value'),
            enabled: hasCheckboxes ? item.getAttribute('data-enabled') === 'true' : true
        }));
    }

    /**
     * Gets the items to display, merging state and config data
     * @returns {Array} Array of item objects with value and enabled properties
     */
    getItems() {
        const stateItems = this.myState.value;
        const configItems = this.config.items || [];

        // if we have state data with enabled/disabled info, use it
        if (stateItems && Array.isArray(stateItems) && stateItems.length > 0 &&
            typeof stateItems[0] === 'object' && 'enabled' in stateItems[0]) {
            return stateItems;
        }

        // if we have simple array state (just values), merge with config
        if (stateItems && Array.isArray(stateItems)) {
            return stateItems.map(value => ({
                value: value,
                enabled: true
            }));
        }

        // fall back to config items, enabled by default
        return configItems.map(item => ({
            value: item,
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
        if (this.#hasCheckboxes()) {
            this.addEventListener('change', this.handleToggleChange.bind(this));
        }
    }

    /**
     * Handles the start of a drag operation
     * @param {DragEvent} e The drag start event
     */
    handleDragStart(e) {
        if (!e.target.classList.contains('sortable-item')) return;
        if (e.target.getAttribute('data-enabled') === 'false' ||
            e.target.getAttribute('data-fixed') === 'true') {
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

        // Prevent placing before a fixed-first item or after a fixed-last item
        const children = [...sortableList.querySelectorAll('.sortable-item')];
        const firstChild = children[0];
        const lastChild = children[children.length - 1];

        if (afterElement == null) {
            // Would append to end — block if last item is fixed
            if (this.config.fixLast && lastChild && lastChild.getAttribute('data-fixed') === 'true') {
                sortableList.insertBefore(dragging, lastChild);
            } else {
                sortableList.appendChild(dragging);
            }
        } else if (this.config.fixFirst && afterElement === firstChild && firstChild.getAttribute('data-fixed') === 'true') {
            // Would insert before the fixed first item — place after it instead
            sortableList.insertBefore(dragging, firstChild.nextSibling);
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
        if (!this.#hasCheckboxes() || !e.target.matches('input[type="checkbox"][data-toggle-item]')) return;

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
            label.classList.remove('has-text-dark');
        } else {
            label.classList.add('has-text-dark');
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

    #hasCheckboxes() {
        return this.config.checkboxes === true;
    }

    /**
     * Ensures the sorter state matches the current schema.
     *
     * - Converts legacy primitive entries into `{value, enabled}` objects.
     * - Forces all items to `enabled: true` when checkboxes are disabled.
     * - Re-seeds state from config items if no values were saved.
     *
     * @returns {Array|null} Normalized items or `null` when no change was needed.
     */
    #normalizeStateItems() {
        const hasCheckboxes = this.#hasCheckboxes();
        const stateItems = this.myState.value;
        const configItems = this.config.items || [];

        if (!stateItems) {
            return configItems.map(item => ({
                value: item,
                enabled: true
            }));
        }

        if (!Array.isArray(stateItems) || stateItems.length === 0) {
            return configItems.map(item => ({
                value: item,
                enabled: true
            }));
        }

        const needsNormalization = stateItems.some(item => typeof item !== 'object' || item === null || !('value' in item));
        const shouldEnableAll = !hasCheckboxes && stateItems.some(item => item && item.enabled === false);
        const needsFixEnforce = this.config.fixFirst || this.config.fixLast;

        if (!needsNormalization && !shouldEnableAll && !needsFixEnforce) {
            return null;
        }

        let items = stateItems.map(item => ({
            value: (typeof item === 'object' && item !== null && 'value' in item) ? item.value : item,
            enabled: hasCheckboxes ? (typeof item === 'object' && item !== null && item.enabled === false ? false : true) : true
        }));

        // Ensure fixed items are in their correct positions
        if (this.config.fixFirst && configItems.length > 0) {
            const fixedValue = configItems[0];
            const idx = items.findIndex(i => i.value === fixedValue);
            if (idx > 0) {
                const [fixedItem] = items.splice(idx, 1);
                fixedItem.enabled = true;
                items.unshift(fixedItem);
            } else if (idx === 0) {
                items[0].enabled = true;
            }
        }

        if (this.config.fixLast && configItems.length > 0) {
            const fixedValue = configItems[configItems.length - 1];
            const idx = items.findIndex(i => i.value === fixedValue);
            if (idx >= 0 && idx < items.length - 1) {
                const [fixedItem] = items.splice(idx, 1);
                fixedItem.enabled = true;
                items.push(fixedItem);
            } else if (idx === items.length - 1) {
                items[items.length - 1].enabled = true;
            }
        }

        return items;
    }
}

customElements.define('sorter-component', SorterComponent);
