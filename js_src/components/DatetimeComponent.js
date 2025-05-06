import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';
import {U} from '../U.js';

export class DatetimeComponent extends BaseComponent {

    html() {
        const field = U.createField(this.config);

        const control = document.createElement("div");
        control.classList.add("control");

        field.appendChild(control);

        const input = document.createElement("input");
        input.type = "datetime-local";
        input.name = this.name;
        input.classList.add("input");
        input.placeholder = this.config.placeholder || "";
        input.value = this.myState.value ?? null;
        input.readOnly = !!this.config.readonly;

        control.appendChild(input);

        return field;
    }
}

customElements.define('datetime-component', DatetimeComponent);
