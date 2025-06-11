import {BaseComponent} from './BaseComponent.js';
import SignaturePad from 'signature_pad';
import {U} from "../U";

export class SignatureComponent extends BaseComponent {

    html() {

        const wrapper = document.createElement("div");
        wrapper.classList.add("signature-pad");

        const field = U.createField(this.config, [], true);
        wrapper.appendChild(field);

        const control = document.createElement("div");
        control.classList.add("control");

        // actual input field to be submitted with the form
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = this.name;
        control.appendChild(input);

        field.appendChild(control);

        // dynamic canvas to draw on
        const canvas = document.createElement("canvas");
        const signaturePad = new SignaturePad(canvas);
        // default input event is of no use here
        signaturePad.addEventListener("endStroke", () => {
            this.myState.value = signaturePad.toDataURL();
            input.value = this.myState.value;
        });

        // static image with saved signature
        const imageDiv = document.createElement("div");
        const image = document.createElement("img");

        // display canvas or image depending on available data
        let signatureVisibleElement;
        if (!this.myState.value) {
            signatureVisibleElement = canvas;
        } else {
            image.src = this.myState.value;
            imageDiv.appendChild(image);
            signatureVisibleElement = imageDiv;
        }
        control.appendChild(signatureVisibleElement);

        // clear button
        const clearButtonField = document.createElement("div");
        clearButtonField.classList.add("field");

        const clearButtonControl = document.createElement("div");
        clearButtonControl.classList.add("control");

        clearButtonField.appendChild(clearButtonControl);

        const clearButton = document.createElement("button");
        clearButton.classList.add("button", "clear");
        clearButton.type = "button";
        clearButton.innerText = U.getLang('label_signature_delete');
        clearButton.addEventListener("click",  (event) => {
            // clear all values
            signaturePad.clear();
            this.myState.value = null;
            input.value = null;

            // restore canvas (signature may be an image at this point)
            control.removeChild(signatureVisibleElement);
            signatureVisibleElement = canvas;
            control.appendChild(signatureVisibleElement);
        });

        clearButtonControl.appendChild(clearButton);

        wrapper.appendChild(clearButtonField);

        return wrapper;
    }
}

customElements.define('signature-component', SignatureComponent);
