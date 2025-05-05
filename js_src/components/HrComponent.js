import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';

export class HrComponent extends BaseComponent {

    html() {
        return html`
            <hr style="background-color: ${this.config.color ?? 'silver'}; height: ${this.config.height ?? 1}px;">
        `;
    }

    executeValidators() {
        return true;
    }
}

customElements.define('hr-component', HrComponent);
