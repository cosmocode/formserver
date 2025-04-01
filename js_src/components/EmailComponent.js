import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';
import {U} from '../U.js';

export class EmailComponent extends BaseComponent {
    html() {
        return html`
            <div class="field">
                <label class="label">${this.config.label}${U.requiredMark(this.config)}</label>$${U.tooltipHint(this.config)}
                <div class="control">
                    <input class="input" type="email" name="${this.name}"
                           ${U.attrHTML('placeholder', this.config.placeholder, false)}
                           value="${this.myState.value ?? ''}"
                           ${this.config.readonly ? 'readonly' : ''}>
                </div>
            </div>
        `;
    }
}

customElements.define('email-component', EmailComponent);
