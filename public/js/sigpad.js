/**
 * Init Signature Pad
 */

const init = function () {
    const form = document.getElementById("form");
    const replaceButons = document.querySelectorAll('button.sigreplace');
    const wrappers = document.querySelectorAll(".signature-pad");

// attach event listeners to replace buttons
    Array.from(replaceButons).forEach(function(button) {
        button.addEventListener('click', function(event) {
            const id = this.dataset.replacebutton;

            const replacePad = document.querySelector('[data-replacepad=' + id + ']');
            replacePad.classList.remove('hidden');
            initSignaturePad(replacePad);

            const replaceContainer = document.querySelector('[data-replacecontainer=' + id + ']');
            replaceContainer.classList.add('hidden');
        });
    });

// initialize all (visible) fields
    Array.from(wrappers).forEach(function(wrapper) {
        initSignaturePad(wrapper);
    });
};


/**
 * Initializes a clean signature pad for each visible signature element
 *
 * @param wrapper
 */
function initSignaturePad(wrapper) {
    const canvas = wrapper.querySelector("canvas");
    const clearButton = wrapper.querySelector("[data-action=clear]");
    const dataField = wrapper.querySelector("input");

    if (wrapper && canvas && !wrapper.classList.contains('hidden')) {
        // reset field value
        dataField.value = "";
        const signaturePad = new SignaturePad(canvas, { backgroundColor: "rgb(255,255,255)" });

        // Set height and width
        const width = wrapper.dataset.width;
        if (width) {
            canvas.width = width;
        }
        const height = wrapper.dataset.height;
        if (height) {
            canvas.height = height;
        }

        // Add event listeners
        clearButton.addEventListener("click", function (event) {
            signaturePad.clear();
            dataField.value = "";
        });

        form.addEventListener("submit", function (event) {
            if (signaturePad.isEmpty()) {
                dataField.value = '';
            } else {
                dataField.value = signaturePad.toDataURL();
            }
        });
    }
}

export {init, initSignaturePad};
