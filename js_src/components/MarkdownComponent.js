import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';
import {U} from "../U";

export class MarkdownComponent extends BaseComponent {

    html() {

        const field = U.createField(this.config);

        const control = document.createElement("div");
        control.classList.add("control", "with-tooltip");
        control.innerHTML = this.config.markdown;

        // tooltips and modals get appended to markdown, not label
        control.insertAdjacentHTML('beforeend', U.tooltipHint(this.config));
        if (this.config.modal) {
            control.appendChild(U.modalHint(this.config));
            control.appendChild(U.modal(this.config));
        }
        field.appendChild(control);

        return field;
    }

    executeValidators() {
        return true;
    }
}

customElements.define('markdown-component', MarkdownComponent);
