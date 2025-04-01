import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';
import {U} from '../U.js';

export class TextareaComponent extends BaseComponent {
    html() {
        return html`
            <div class="field">
                <label class="label">${this.config.label}${U.requiredMark(this.config)}</label>$${U.tooltipHint(this.config)}
                <div class="control">
                    <textarea class="textarea"
                        ${U.attrHTML('placeholder', this.config.placeholder, false)}
                        ${U.attrHTML('cols', this.config.cols, false)}
                        ${U.attrHTML('rows', this.config.rows, false)}
                        name="${this.name}" ${this.config.readonly ? 'readonly' : ''}>${this.myState.value ?? ''}</textarea>
                </div>
            </div>
        `;
    }
}

customElements.define('textarea-component', TextareaComponent);
