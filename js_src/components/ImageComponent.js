import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';

export class ImageComponent extends BaseComponent {

    html() {
        return html`
            <div class="field">
                <div class="control">
                    FIXME image
                </div>
            </div>
        `;
    }

    executeValidators() {
        return true;
    }
}

customElements.define('image-component', ImageComponent);
