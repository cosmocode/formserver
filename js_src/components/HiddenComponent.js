import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';

export class HiddenComponent extends BaseComponent {

    html() {
        return html`
            <input type="hidden" name="${this.config.name}" value="${this.myState.value}" />
        `;
    }

    executeValidators() {
        return true;
    }
}

customElements.define('hidden-component', HiddenComponent);
