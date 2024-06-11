/**
 * Init upload feedback text
 */
export function uploadFeedback () {
    Array.from(document.querySelectorAll('.form-input.file-input')).forEach(function(fileUpload) {
        fileUpload.addEventListener('input', function (e) {
            const infoContainerId = e.target.getAttribute('data-info-container-id');
            const infoContainer = document.getElementById(infoContainerId);
            const errorContainerId = e.target.getAttribute('data-error-container-id');
            const errorContainer = document.getElementById(errorContainerId);

            const max = e.target.getAttribute('data-max');
            const curFiles = this.files;

            let uploadSize = 0;

            for (const file of curFiles) {
                uploadSize += file.size;
            }

            const ok = uploadSize < max;

            if (!ok) {
                this.value = ''
                errorContainer.classList.remove('hidden');
                infoContainer.classList.add('hidden');
            } else {
                errorContainer.classList.add('hidden');
                infoContainer.classList.remove('hidden');

            }

        })
    });
}
