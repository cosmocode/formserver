import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';

export class MarkdownComponent extends BaseComponent {

    html() {
        return html`
            <div class="field">
                <div class="control">
                    ${this.config.markdown}
                </div>
            </div>
        `;
    }

    executeValidators() {
        return true;
    }
}

customElements.define('markdown-component', MarkdownComponent);
