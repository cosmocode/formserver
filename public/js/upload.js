/**
 * Init upload feedback text
 */
export function uploadFeedback () {
    // regular upload using the upload button
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
                infoContainer.querySelector('.notification-upload').innerText = listUploadedFiles(curFiles);
            }
        });
    });

    // upload using drag events
    Array.from(document.querySelectorAll('.field.file.dropzone')).forEach(function(fileDropzone) {
        fileDropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            e.target.classList.remove('highlight');

            let fileInput = e.target.querySelector('.form-input.file-input');
            fileInput.files = e.dataTransfer.files;

            const infoContainerId = fileInput.getAttribute('data-info-container-id');
            const infoContainer = document.getElementById(infoContainerId);

            infoContainer.classList.remove('hidden');
            infoContainer.querySelector('.notification-upload').innerText = listUploadedFiles(fileInput.files);
        });

        fileDropzone.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.stopPropagation();
            e.target.classList.add('highlight');
        });

        fileDropzone.addEventListener('dragleave', function (e) {
            e.preventDefault();
            e.stopPropagation();
            e.target.classList.remove('highlight');
        });
    });

    // list filenames of current upload candidates
    function listUploadedFiles(curFiles) {
        let output = '';
        for (const file of curFiles) {
            output += `${file.name}\n`;
        }
        return output;
    }
}
