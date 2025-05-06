import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';
import {U} from '../U.js';

export class TextareaComponent extends BaseComponent {
    html() {

        const field = U.createField(this.config);

        const control = document.createElement("div");
        control.classList.add("control");

        field.appendChild(control);

        const input = document.createElement("textarea");
        input.name = this.name;
        input.classList.add("textarea");
        input.placeholder = this.config.placeholder || "";
        input.value = this.myState.value ?? null;
        input.readOnly = !!this.config.readonly;
        if (this.config.cols) {
            input.cols = this.config.cols;
        }
        if (this.config.rows) {
            input.rows = this.config.rows;
        }

        control.appendChild(input);

        return field;

    }
}

customElements.define('textarea-component', TextareaComponent);
