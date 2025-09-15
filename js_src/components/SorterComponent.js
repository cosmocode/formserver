import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';

/**
 * Sorter component for drag-and-drop sorting of items
 */
export class SorterComponent extends BaseComponent {

    html() {
        const field = U.createField(this.config);

        const control = document.createElement("div");
        control.classList.add("control");

        const sortableList = document.createElement("ul");
        sortableList.classList.add("sortable-list");
        sortableList.setAttribute("draggable", "false");

        // Add flex class if alignment is horizontal
        if (this.config.alignment === 'horizontal') {
            sortableList.classList.add("is-flex", "sortable-list-horizontal");
        }

        // Get current sorted items from state or use default items
        const items = this.myState.value || this.config.items || [];

        items.forEach((item, index) => {
            const listItem = document.createElement("li");
            listItem.classList.add("sortable-item", "box", "is-flex", "is-align-items-center", "p-3", "m-2");
            listItem.setAttribute("draggable", "true");
            listItem.setAttribute("data-value", item.value || item);
            listItem.style.cursor = "grab";
            listItem.innerHTML = `
                <span class="drag-handle icon is-small mr-2" style="cursor: grab;">
                    <svg width="12" height="16" viewBox="0 0 12 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="3" cy="3" r="1.5" fill="#999"/>
                        <circle cx="9" cy="3" r="1.5" fill="#999"/>
                        <circle cx="3" cy="8" r="1.5" fill="#999"/>
                        <circle cx="9" cy="8" r="1.5" fill="#999"/>
                        <circle cx="3" cy="13" r="1.5" fill="#999"/>
                        <circle cx="9" cy="13" r="1.5" fill="#999"/>
                    </svg>
                </span>
                <span class="item-label has-text-weight-medium">${item.label || item}</span>
            `;
            sortableList.appendChild(listItem);
        });

        control.appendChild(sortableList);
        field.appendChild(control);

        return field;
    }

    registerStateChangeHandler() {
        // Override to handle drag and drop events instead of input events
        this.addEventListener('dragstart', this.handleDragStart.bind(this));
        this.addEventListener('dragover', this.handleDragOver.bind(this));
        this.addEventListener('drop', this.handleDrop.bind(this));
        this.addEventListener('dragend', this.handleDragEnd.bind(this));
    }

    handleDragStart(e) {
        if (!e.target.classList.contains('sortable-item')) return;

        e.target.classList.add('dragging');
        e.target.style.cursor = "grabbing";
        e.target.style.opacity = "0.5";
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', e.target.outerHTML);
        e.dataTransfer.setData('text/plain', e.target.getAttribute('data-value'));
    }

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

    handleDrop(e) {
        e.preventDefault();
        this.updateStateFromSortedList();
    }

    handleDragEnd(e) {
        if (e.target.classList.contains('sortable-item')) {
            e.target.classList.remove('dragging');
            e.target.style.cursor = "grab";
            e.target.style.opacity = "1";
        }
    }

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

    updateStateFromSortedList() {
        const sortableList = this.querySelector('.sortable-list');
        if (!sortableList) return;

        const items = [...sortableList.querySelectorAll('.sortable-item')];
        const sortedValues = items.map(item => item.getAttribute('data-value'));

        this.myState.value = sortedValues;
    }

    executeValidators() {
        super.executeValidators();
        // Add any sorter-specific validation here if needed
    }
}

// Register the custom element
customElements.define('sorter-component', SorterComponent);
