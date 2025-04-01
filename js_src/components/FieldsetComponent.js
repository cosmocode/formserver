import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';

export class FieldsetComponent extends BaseComponent {
    html() {
        const fieldSetElement = document.createElement('fieldset');
        // fieldsets need a name for building full dotted paths
        fieldSetElement.name = this.config.name;
        fieldSetElement.classList.add('box');

        if (this.config.label) {
            fieldSetElement.insertAdjacentHTML('afterbegin', `<legend>${this.config.label}</legend>`);
        }

        U.attachChildren(fieldSetElement, this.config['children'], this.myState.state);

        return fieldSetElement;
    }


    executeValidators() {
        return true;
    }
}

customElements.define('fieldset-component', FieldsetComponent);
