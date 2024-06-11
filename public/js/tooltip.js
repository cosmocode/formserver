/**
 * Move tooltips inside headings for better styling
 */
export function tooltips() {
    Array.from(document.querySelectorAll('.with-tooltip .control button.tooltip-button')).forEach(function(elem) {
        const prev = elem.previousElementSibling;
        const headlines = ['H1', 'H2', 'H3', 'H4', 'H5', 'H6'];
        if (headlines.includes(prev.tagName)) {
            prev.appendChild(elem);
        }
    });
}
