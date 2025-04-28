import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';

export class PagesComponent extends BaseComponent {
    html() {
        const pagesElement = document.createElement("div");

        pagesElement.classList.add("box");

        if (this.config.label) {
            pagesElement.insertAdjacentHTML("afterbegin", `<div class="label">${this.config.label}</div>`);
        }

        // wraps tabs panel and content elements
        const tabsContainer = document.createElement("div");
        tabsContainer.classList.add("tabs-container");
        // containers need a name for building full dotted paths
        tabsContainer.name = this.config.name;

        const tabs = document.createElement("div");
        tabs.classList.add("tabs");

        tabsContainer.appendChild(tabs);

        const tabItems = document.createElement("ul");
        let isFirstTab = true;
        let activeTabTarget;
        Object.keys(this.config.children).forEach(key => {
            const tab = document.createElement("li");
            const tabTarget = `${this.config.name}.${key}`;
            if (isFirstTab) {
                activeTabTarget = tabTarget;
                tab.classList.add("is-active");
                isFirstTab = false;
            }
            const link = document.createElement("a");
            link.innerText = this.config.children[key].label;
            link.href = `#${tabTarget}`;
            link.dataset.target = tabTarget;
            link.addEventListener("click", this.#switchTabs);
            tab.appendChild(link);
            tabItems.appendChild(tab);
        });

        tabs.appendChild(tabItems);

        U.attachChildren(tabsContainer, this.config["children"], this.myState.state);

        // hide children other than target of active tab
        // escape dots in IDs so they are not interpreted as CSS classes
        const selector = `fieldset:not(#${activeTabTarget.replace(/\./g, '\\.')})`;
        tabsContainer.querySelectorAll(selector).forEach(child => {
            child.style.display = "none";
        });

        pagesElement.appendChild(tabsContainer);

        return pagesElement;
    }


    executeValidators() {
        return true;
    }

    /**
     * Switches tabs state and children visibility
     * @param {Event} e
     */
    #switchTabs(e) {
        e.preventDefault();
        e.stopPropagation();
        const targetId = e.target.dataset.target;
        const li = e.target.parentNode;
        li.classList.add("is-active");
        document.getElementById(targetId).style.display = "block";

        li.parentNode.childNodes.forEach(tab => {
            if (tab.firstChild !== e.target) {
                tab.classList.remove("is-active");
                document.getElementById(tab.firstChild.dataset.target).style.display = "none";
            }
        });
    }
}

customElements.define('pages-component', PagesComponent);
