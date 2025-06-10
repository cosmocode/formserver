import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';

export class TableComponent extends BaseComponent {
    html() {
        const tableElement = document.createElement('table');
        // like fieldsets, tables need a name for building full dotted paths
        tableElement.name = this.config.name;
        tableElement.classList.add('table');

        const headRow = this.#getHeadRow();

        tableElement.appendChild(headRow);

        U.attachTableRows(tableElement, this.config['children'], this.myState.state, headRow.children.length - 1); // first child is empty

        return tableElement;
    }

    /**
     * Create row with column headers
     *
     * @returns {HTMLTableRowElement}
     */
    #getHeadRow() {
        // named headers have priority, use them as is
        let columnHeaders = this.config.headers || [];

        // add more unnamed headers if needed
        if (this.config.repeat) {
            let missing = parseInt(this.config.repeat) - columnHeaders.length;
            for (let i = 0; i < missing; i++) {
                columnHeaders.push(`${this.config.label} ${columnHeaders.length + 1}`); // Default content, or 'Column ' + (i + 1)
            }
        }

        // prepend an empty header for the column with field names
        columnHeaders.unshift("");

        const headRow = document.createElement('tr');

        for (const header of columnHeaders) {
            const th = document.createElement('th');
            th.innerText = header;
            headRow.appendChild(th);
        }

        return headRow;
    }

    /** @override */
    htmlWrap(html) {
        const wrapper = document.createElement('div');
        if (this.config.scrollable) {
            wrapper.classList.add('table-container');
        }
        wrapper.appendChild(html);

        return wrapper;
    }

    executeValidators() {
        return true;
    }
}

customElements.define('table-component', TableComponent);
