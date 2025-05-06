import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';
import {U} from '../U.js';

export class EmailComponent extends BaseComponent {
    html() {
        const field = U.createField(this.config);

        const control = document.createElement("div");
        control.classList.add("control");

        field.appendChild(control);

        const input = document.createElement("input");
        input.type = "email";
        input.name = this.name;
        input.classList.add("input");
        input.placeholder = this.config.placeholder || "";
        input.value = this.myState.value ?? null;
        input.readOnly = !!this.config.readonly;

        control.appendChild(input);

        return field;
    }
}

customElements.define('email-component', EmailComponent);
