// Inject flatpickr
flatpickr('[data-calender-type="date"]', {'dateFormat' : 'd.m.Y', 'allowInput' : true});
flatpickr('[data-calender-type="time"]', {'noCalendar' : true, 'enableTime' : true, 'time_24hr' : true, 'allowInput' : true});
flatpickr('[data-calender-type="datetime"]', {'enableTime' : true, 'time_24hr' : true, 'dateFormat' : 'd.m.Y H:i', 'allowInput' : true});

/**
 * Signature Pad
 */

var form = document.getElementById("form");
var wrapper = document.getElementById("signature-pad");
var canvas = document.querySelector("canvas");
var clearButton = wrapper.querySelector("[data-action=clear]");
var dataField = wrapper.querySelector("input");

if (wrapper && canvas) {
    var signatureData = dataField.value;
    var signaturePad = new SignaturePad(canvas, { backgroundColor: "rgb(255,255,255)" });

    if (signatureData !== '') {
        signaturePad.fromDataURL(signatureData);
    }

    clearButton.addEventListener("click", function (event) {
        signaturePad.clear();
        dataField.value = "";
    });

    form.addEventListener("submit", function (event) {
        dataField.value = signaturePad.toDataURL();
    });
}
