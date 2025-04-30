import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';
import {U} from "../U";

export class ImageComponent extends BaseComponent {

    html() {
        const field = U.createField(this.config);

        const control = document.createElement("div");
        control.classList.add("control");

        field.appendChild(control);

        const img = document.createElement("img");
        img.src = this.config.src;

        control.appendChild(img);

        return field;
    }

    executeValidators() {
        return true;
    }
}

customElements.define('image-component', ImageComponent);
