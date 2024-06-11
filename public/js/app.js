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

/**
 * Init flatpickr
 */
flatpickr('input:read-write[data-calendar-type="date"]', {'dateFormat' : 'd.m.Y', 'allowInput' : true});
flatpickr('input:read-write[data-calendar-type="time"]', {'noCalendar' : true, 'enableTime' : true, 'time_24hr' : true, 'allowInput' : true});
flatpickr('input:read-write[data-calendar-type="datetime"]', {'enableTime' : true, 'time_24hr' : true, 'dateFormat' : 'd.m.Y H:i', 'allowInput' : true});
