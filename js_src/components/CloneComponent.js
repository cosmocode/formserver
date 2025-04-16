import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';

export class CloneComponent extends BaseComponent {
    html() {
        const cloneElement = document.createElement('div');
        // all container elements need a name for building full dotted paths
        cloneElement.name = this.config.name;
        cloneElement.classList.add('box');

        if (this.config.label) {
            cloneElement.insertAdjacentHTML('afterbegin', `<div class="label">${this.config.label}</div>`);
        }

        U.attachCloneableChildren(cloneElement, this.config['children'], this.myState.state);

        cloneElement.appendChild(this.#getCloneButton());

        return cloneElement;
    }

    #getCloneButton() {
        const buttonField = document.createElement("div");
        buttonField.classList.add("field");

        const buttonControl = document.createElement("div");
        buttonControl.classList.add("control");

        buttonField.appendChild(buttonControl);

        const cloneButton = document.createElement("button");
        cloneButton.classList.add("button", "is-inverted");
        cloneButton.type = "button";
        cloneButton.innerText = U.getLang('button_clone') + " + ";
        cloneButton.addEventListener("click",  (event) => {
            console.log('would create another child of ' + this.name);
        });

        buttonControl.appendChild(cloneButton);

        return buttonField;
    }


    executeValidators() {
        return true;
    }
}

customElements.define('clone-component', CloneComponent);
