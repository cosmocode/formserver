import {BaseComponent} from './BaseComponent.js';
import {U} from "../U";

export class DownloadComponent extends BaseComponent {

    html() {
        const field = U.createField(this.config, [], null, true);

        const control = document.createElement("div");
        control.classList.add("control");

        field.appendChild(control);

        const link = document.createElement("a");
        link.href = this.config.href;
        link.target = "_blank";
        link.innerText = this.config.label;

        control.appendChild(link);

        // tooltips and modals get appended to links, not label
        control.insertAdjacentHTML('beforeend', U.tooltipHint(this.config));
        if (this.config.modal) {
            control.appendChild(U.modalHint(this.config));
            control.appendChild(U.modal(this.config));
        }

        return field;
    }

    executeValidators() {
        return true;
    }
}

customElements.define('download-component', DownloadComponent);
