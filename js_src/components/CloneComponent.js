import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';

export class CloneComponent extends BaseComponent {
    html() {
        const cloneElement = document.createElement('div');
        // all container elements need a name for building full dotted paths
        cloneElement.name = `${this.config.name}`;
        cloneElement.id = this.name;
        cloneElement.classList.add('box');

        if (this.config.label) {
            cloneElement.insertAdjacentHTML('afterbegin', `<div class="label">${this.config.label}</div>`);
        }

        const childrenConfig = this.config["children"];
        const count = this.myState.value ? this.myState.value.length : 0;
        this.#attachCloneableChildren(cloneElement, childrenConfig, this.myState.state, count);

        cloneElement.appendChild(this.#getCloneButton(cloneElement, this.config.label || U.getLang("button_clone")));

        return cloneElement;
    }

    #cloneCallback(e, cloneElement) {
        const wrapper = cloneElement.querySelector(".clone-elements");
        const index = this.myState.value ? this.myState.value.length : 0;
        this.#doAttach(wrapper, this.config["children"], this.name, index, this.myState.state);
    }

    #getCloneButton(cloneElement, label) {
        const buttonField = document.createElement("div");
        buttonField.classList.add("field");

        const buttonControl = document.createElement("div");
        buttonControl.classList.add("control");

        buttonField.appendChild(buttonControl);

        const cloneButton = document.createElement("button");
        cloneButton.classList.add("button", "is-inverted");
        cloneButton.type = "button";
        cloneButton.innerText = `${label} + `;
        cloneButton.dataset.cloneContainer = cloneElement.getAttribute("id");
        cloneButton.addEventListener("click",  (e) => this.#cloneCallback(e, cloneElement));

        buttonControl.appendChild(cloneButton);

        return buttonField;
    }

    #attachCloneableChildren(parentElement, childrenConfig, state, count) {
        let index = 0;

        // an additional wrapper is needed for Bulma columns
        const columnWrapper = document.createElement('div');
        columnWrapper.classList.add("columns", "is-multiline", "clone-elements");
        parentElement.appendChild(columnWrapper);

        do {
            this.#doAttach(columnWrapper, childrenConfig, parentElement.name, index, state);
            index++;
        } while (index < count);
    }

    #doAttach(wrapper, childrenConfig, parentName, index, state) {
        Object.keys(childrenConfig).forEach(key => {
            const fieldConfig = childrenConfig[key];
            fieldConfig.name = `${parentName}[${index}].${key}`; // add full dotted field ID to config
            if (fieldConfig.label) {
                fieldConfig.label = `${fieldConfig.label}`;
            }
            const element = document.createElement(`${fieldConfig.type}-component`);
            element.initialize(state, fieldConfig);

            wrapper.appendChild(element);
            element.render(); // initial rendering
        });
    }

    executeValidators() {
        return true;
    }
}

customElements.define('clone-component', CloneComponent);
