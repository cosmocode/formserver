import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';

export class TableComponent extends BaseComponent {
    html() {
        const tableElement = document.createElement('table');
        // like fieldsets, tables need a name for building full dotted paths
        tableElement.name = this.config.name;
        tableElement.classList.add('table');

        const repeat = parseInt(this.config.repeat);
        const columnHeaders = U.getTableColumnHeaders(this.config.label, repeat);

        const thead = document.createElement('thead');
        const headRow = document.createElement('tr');

        for (const header of columnHeaders) {
            const th = document.createElement('th');
            th.innerText = header;
            headRow.appendChild(th);
        }
        tableElement.appendChild(headRow);

        U.attachTableRows(tableElement, this.config['children'], this.myState.state, repeat);

        return tableElement;
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
