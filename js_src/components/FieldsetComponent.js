import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';
import html from "html-template-tag";

export class FieldsetComponent extends BaseComponent {
    html() {
        const fieldSetElement = document.createElement('fieldset');
        // fieldsets need a name for building full dotted paths
        fieldSetElement.name = this.config.name;
        fieldSetElement.id = this.config.name;

        if (this.config.label) {
            const label = document.createElement("div");
            label.classList.add("label");
            if (this.config.labelsmall) {
                label.classList.add("label-smaller");
            }
            label.innerText = this.config.label;
            fieldSetElement.appendChild(label);

            fieldSetElement.insertAdjacentHTML('beforeend', U.tooltipHint(this.config));
            if (this.config.modal) {
                fieldSetElement.appendChild(U.modalHint(this.config));
                fieldSetElement.appendChild(U.modal(this.config));
            }
        }

        U.attachChildren(fieldSetElement, this.config['children'], this.myState.state);

        return fieldSetElement;
    }

    /** @override */
    htmlWrap(html) {
        const wrapper = document.createElement('div');
        const classes = this.config.column ?? "is-full";
        wrapper.classList.add('column',  ...classes.split(/\s+/));

        // custom background as Bulma color name or CSS color
        if (this.config.backgroundName) {
            wrapper.classList.add(`has-background-${this.config.backgroundName}`);
        }
        if (this.config.backgroundNumber) {
            wrapper.style.backgroundColor = this.config.backgroundNumber;
        }
        wrapper.appendChild(html);

        return wrapper;
    }

    executeValidators() {
        return true;
    }
}

customElements.define('fieldset-component', FieldsetComponent);
