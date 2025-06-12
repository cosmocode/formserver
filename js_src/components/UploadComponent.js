import {BaseComponent} from './BaseComponent.js';
import html from 'html-template-tag';
import {U} from "../U";

export class UploadComponent extends BaseComponent {

    /** @type {FileList} Currently uploaded files */
    #currentFiles;

    html() {
        // not using U.createField() because we need a different structure
        const field = document.createElement("div");
        field.classList.add("field", "file", "dropzone");

        const control = document.createElement("div");
        control.classList.add("control");

        const label = document.createElement("label");
        label.classList.add("label");
        if (this.config.labelsmall) {
            label.classList.add("label-smaller");
        }
        label.innerText = html`${this.config.label}` + U.requiredMark(this.config);

        control.appendChild(label);
        control.insertAdjacentHTML('beforeend', U.tooltipHint(this.config));
        if (this.config.modal) {
            control.appendChild(U.modalHint(this.config));
            control.appendChild(U.modal(this.config));
        }
        field.appendChild(control);

        // input container must be a label to trigger upload
        const fileInputContainer = document.createElement("label");
        fileInputContainer.classList.add("buttons", "file-label");

        control.appendChild(fileInputContainer);

        // actual file input and Bulma call-to-action (cta) element
        const input = document.createElement("input");
        input.type = "file";
        input.name = this.config.name;
        input.classList.add("file-input", "form-input");
        input.multiple = true;
        if (!!this.config.validation.fileext) {
            input.accept = this.config.validation.fileext.split(",").map(ext => "." + ext.trim()).join(",");
        }
        if (!!this.config.validation.filesize) {
            input.dataset['max'] = this.config.validation.filesize;
        }

        fileInputContainer.appendChild(input);

        const cta = document.createElement("span");
        cta.classList.add("file-cta");
        fileInputContainer.appendChild(cta);

        const labelSpan = document.createElement("span");
        labelSpan.classList.add("file-label");
        labelSpan.innerText = U.getLang("button_upload");
        cta.appendChild(labelSpan);

        // notification containers
        const infoContainer = document.createElement("div");
        infoContainer.classList.add("notification", "hidden", "is-warning");
        control.appendChild(infoContainer);

        const errorContainer = document.createElement("div");
        errorContainer.classList.add("notification", "hidden", "is-danger");
        control.appendChild(errorContainer);

        // create upload status info from state
        this.#showStatus(infoContainer);

        // button to delete existing uploads
        const deleteButton = document.createElement("button");
        deleteButton.type = "button";
        deleteButton.classList.add("button");
        deleteButton.innerText = U.getLang("button_upload_replace");

        deleteButton.addEventListener("click", e => {
            this.myState.value = null;
            input.value = null;
            this.render();
        });

        infoContainer.appendChild(deleteButton);

        this.attachDragAndDropHandlers(field, input);

        return field;
    }

    /**
     * Add drag & drop handlers
     *
     * @param {HTMLElement} field
     * @param {HTMLElement} input
     */
    attachDragAndDropHandlers(field, input) {
        field.addEventListener("drop", e => {
            e.preventDefault();
            e.stopPropagation();
            e.target.classList.remove("highlight");

            input.files = e.dataTransfer.files;

            // update state on "drop"
            this.updateStateOnInput(e.dataTransfer);
        });

        field.addEventListener("dragover", function (e) {
            e.preventDefault();
            e.stopPropagation();
            e.target.classList.add("highlight");
        });

        field.addEventListener("dragleave", function (e) {
            e.preventDefault();
            e.stopPropagation();
            e.target.classList.remove("highlight");
        });
    }

    /** @override */
    async updateStateOnInput(target) {
        this.#currentFiles = target.files;
        const ok = this.#checkSize();

        // reject upload and don't change state
        if (!ok) {
            const errorNotification = this.querySelector(".notification.is-danger");
            errorNotification.innerText = U.getLang("upload_error");
            errorNotification.classList.remove("hidden");
            return;
        }

        let state = U.stateMultivalue(this.myState.value);

        if (this.#currentFiles.length > 0) {
            for (const file of this.#currentFiles) {
                const dataURI = await this.#getFileDataURI(file);
                state.add(new Object({"file": file.name, "content": dataURI}));
            }
        }

        this.myState.value = state;

        this.#showStatus(this.querySelector(".notification.is-warning"));
    }

    /**
     * Display info about files belonging to this component
     *
     * @param {HTMLElement} infoNotification
     */
    #showStatus(infoNotification) {
        if (!this.myState.value) {
            return;
        }

        const oldUploads = infoNotification.querySelector("ul");
        const newUploads = document.createElement("ul");

        for (const fileInfo of this.myState.value) {
            newUploads.insertAdjacentHTML(
                "beforeend",
                `<li><a href="${fileInfo.content}" download="${fileInfo.file}">${fileInfo.file}</a></li>`)
        }

        if (oldUploads !== null) {
            infoNotification.replaceChild(newUploads, oldUploads);
        } else {
            infoNotification.appendChild(newUploads);
        }
        infoNotification.classList.remove("hidden");
    }

    /**
     * Read file and get its dataURI
     *
     * @param {File} file
     * @returns {Promise<unknown>}
     */
    #getFileDataURI(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.onload = (event) => {
                resolve(event.target.result);
            };

            reader.onerror = (error) => {
                reject(error);
            };

            reader.readAsDataURL(file);
        });
    }

    /**
     * Checks if current uploads are within max size, if defined in validation rules
     *
     * @returns {boolean}
     */
    #checkSize() {
        if (!this.config.validation || !this.config.validation.filesize) {
            return true;
        }

        let uploadSize = 0;
        for (const file of this.#currentFiles) {
            uploadSize += file.size;
        }
        return uploadSize < this.config.validation.filesize;
    }
}

customElements.define('upload-component', UploadComponent);
