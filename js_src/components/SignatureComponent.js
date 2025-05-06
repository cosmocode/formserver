import {BaseComponent} from './BaseComponent.js';
import SignaturePad from 'signature_pad';
import {U} from "../U";

export class SignatureComponent extends BaseComponent {

    html() {

        const wrapper = document.createElement("div");
        wrapper.classList.add("signature-pad");

        const field = document.createElement("div");
        field.classList.add("field");

        const control = document.createElement("div");
        control.classList.add("control");

        field.appendChild(control);

        const label = document.createElement("div");
        label.classList.add("label");
        if (this.config.labelsmall) {
            label.classList.add("label-smaller");
        }
        label.innerText = this.config.label + U.requiredMark(this.config);
        control.appendChild(label);

        const tooltip = U.tooltipHint(this.config);
        control.insertAdjacentHTML('beforeend', tooltip);



        const input = document.createElement("input");
        input.type = "hidden";
        input.name = this.name;
        control.appendChild(input);

        const canvas = document.createElement("canvas");
        control.appendChild(canvas);
        wrapper.appendChild(field);

        const signaturePad = new SignaturePad(canvas);
        if (this.myState.value) {
            signaturePad.fromDataURL(this.myState.value);
        }
        // default input event is of no use here
        signaturePad.addEventListener("endStroke", () => {
            this.myState.value = signaturePad.toDataURL();
        });


        const fieldButton = document.createElement("div");
        fieldButton.classList.add("field");

        const controlButton = document.createElement("div");
        controlButton.classList.add("control");

        fieldButton.appendChild(controlButton);

        const clearButton = document.createElement("button");
        clearButton.classList.add("button", "clear");
        clearButton.type = "button";
        clearButton.innerText = U.getLang('label_signature_delete');
        clearButton.addEventListener("click",  (event) => {
            signaturePad.clear();
            this.myState.value = null;
        });

        controlButton.appendChild(clearButton);

        wrapper.appendChild(fieldButton);

        return wrapper;
    }
}

customElements.define('signature-component', SignatureComponent);
