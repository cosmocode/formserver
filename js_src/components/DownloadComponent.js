import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';

export class DownloadComponent extends BaseComponent {

    html() {
        return html`
            <div class="field">
                <div class="control">
                    FIXME download
                </div>
            </div>
        `;
    }

    executeValidators() {
        return true;
    }
}

customElements.define('download-component', DownloadComponent);
