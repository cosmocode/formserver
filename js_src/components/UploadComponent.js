import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';

export class UploadComponent extends BaseComponent {

    html() {
        return html`
            <div class="field">
                <div class="control">
                    FIXME upload
                </div>
            </div>
        `;
    }
}

customElements.define('upload-component', UploadComponent);
