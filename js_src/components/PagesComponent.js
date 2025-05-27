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

        const pages = document.createElement("div");
        pages.classList.add("pages");
        tabsContainer.appendChild(pages);

        const tabItems = document.createElement("ul");

        let i = 0;
        Object.keys(this.config.children).forEach(key => {
            const tab = document.createElement("li");

            const link = document.createElement("a");
            let linkText = `${i + 1}`;
            if (this.config.label) {
                linkText = this.config.label + ` ${linkText}`;
            }
            if (this.config.headers && this.config.headers[i]) {
                linkText = this.config.headers[i];
            } else if (this.config.children[key].label) {
                linkText = this.config.children[key].label;
            }
            link.innerText = linkText;
            link.href = "#";
            link.addEventListener("click", (e) => this.#switchTabs(tab, page, e));
            tab.appendChild(link);
            tabItems.appendChild(tab);

            const page = document.createElement("div");
            page.classList.add("page");
            page.style.display = "none";

            if (i === 0) {
                tab.classList.add("is-active");
                page.style.display = "";
            }

            U.attachChildren(page, this.config.children[key].children, this.myState.state);
            pages.appendChild(page);

            i++;
        });

        tabs.appendChild(tabItems);
        pagesElement.appendChild(tabsContainer);

        return pagesElement;
    }


    executeValidators() {
        return true;
    }

    /**
     * Switches tabs state and children visibility
     * @param {HTMLElement} tab
     * @param {HTMLElement} page
     * @param {Event} e
     */
    #switchTabs(tab, page, e) {
        e.preventDefault();
        e.stopPropagation();

        tab.parentNode.childNodes.forEach(tb => {
            tb.classList.remove("is-active");
        });

        page.parentNode.childNodes.forEach(pg => {
            pg.style.display = "none";
        });

        e.target.parentNode.classList.add("is-active");
        page.style.display = "block";
    }
}

customElements.define('pages-component', PagesComponent);
