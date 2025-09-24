import {BaseComponent} from './BaseComponent.js';
import {U} from '../U.js';
import {ValidatorError} from '../ValidatorError.js';

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

        const tabs = document.createElement("div");
        tabs.classList.add("tabs");
        tabsContainer.appendChild(tabs);

        const pages = document.createElement("div");
        pages.classList.add("pages");
        // all containers need a name
        pages.name = this.config.name;
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
            // all containers need a name for full dotted paths
            page.name = `${pages.name}.${key}`;
            // debugging
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


    /**
     * Custom validation and error handling for pages component,
     * which consists of other components.
     */
    executeValidators() {
        if (!this.visible) {
            return;
        }

        const tabs = this.querySelectorAll('.tabs li');

        // first clear existing error classes
        tabs.forEach(tab => {
            tab.classList.remove('has-errors');
        });

        // validate page child components
        let pagesHaveValidationError = false;
        const pages = this.querySelectorAll('.pages .page');

        pages.forEach((page, pageIndex) => {
            const pageComponents = page.querySelectorAll('.component');

            for (const component of pageComponents) {
                try {
                    component.executeValidators();
                } catch (ValidatorError) {
                    tabs[pageIndex].classList.add('has-errors'); // error class for particular tab
                    pagesHaveValidationError = pagesHaveValidationError || true;
                }
            }
        });

        // custom error hint for the whole component (usually handled in BaseComponent.htmlWrap)
        // throwing a ValidationError would rerender the component and remove error classes from tabs
        if (pagesHaveValidationError) {
            const label = this.querySelector(".box > div.label");
            label.classList.add("has-errors");

            const errorElement = document.createElement('p');
            errorElement.classList.add('help', 'is-danger');
            errorElement.innerText = U.getLang("error_pages");
            label.after(errorElement);
        }
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
