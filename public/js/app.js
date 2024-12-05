import { modals } from "./modal.js";
import { uploadFeedback } from "./upload.js";
import { initClonability } from "./clone.js";
import { tooltips } from "./tooltip.js";
import { initToggles } from "./toggle.js";
import { tablestyle } from "./tablestyle.js";

const form = document.getElementById("form");

const sigPadWrappers = document.querySelectorAll(".signature-pad");
if (sigPadWrappers.length > 0) {
    import('./sigpad.js').then((Module => {
            Module.sigpad(sigPadWrappers);
        }
    ));
}

modals();
uploadFeedback();
initClonability();
tooltips();
initToggles();
tablestyle();
