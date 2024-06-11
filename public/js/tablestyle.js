/*
 * Table-like style
 * Double the width of the first column to fit both labels and input fields
 */
export function tablestyle() {
    Array.from(document.querySelectorAll('.is-left-label .next-is-double')).forEach(function(elem) {
        const colThird = elem.nextElementSibling.querySelector('.is-one-third');
        if (colThird) {
            colThird.classList.remove('is-one-third');
            colThird.classList.add('is-two-thirds');
        }
        const colQuarter = elem.nextElementSibling.querySelector('.is-one-quarter');
        if (colQuarter) {
            colQuarter.classList.remove('is-one-quarter');
            colQuarter.classList.add('is-two-quarters');
        }
        const colFifth = elem.nextElementSibling.querySelector('.is-one-fifth');
        if (colFifth) {
            colFifth.classList.remove('is-one-fifth');
            colFifth.classList.add('is-two-fifths');
        }
    });
}
