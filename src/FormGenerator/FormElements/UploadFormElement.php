<?php
declare(strict_types=1);

namespace CosmoCode\Formserver\FormGenerator\FormElements;

use CosmoCode\Formserver\Exceptions\FormException;
use CosmoCode\Formserver\Helper\FileHelper;
use Slim\Psr7\UploadedFile;

/**
 * Representation of a file upload
 */
class UploadFormElement extends AbstractDynamicFormElement
{
    /**
     * @var string field address that is part of uploaded file
     */
    public string $fieldAddress = '';

    protected string $previousValue = '';

    /**
     * Required for passing client filenames in non-persistent forms.
     * Otherwise those names are stored in values.yaml
     *
     * @var array
     */
    protected array $originalFilenames = [];

    /**
     * @var string
     */
    public string $formDirectory;

    public function __construct(
        string $id,
        array $config,
        FieldsetFormElement $parent = null,
        string $formId = '',
        bool $formIsPersistent = false
    )
    {
        parent::__construct($id, $config, $parent, $formId, $formIsPersistent);
        $this->fieldAddress = $this->getFormElementIdStringified('.');
    }

    /**
     * Additionally update previous value on every upload.
     * Also used when restoring persisted values.
     *
     * @param mixed $value
     */
    public function setValue($value): void
    {
        if (is_null($value)) {
            $this->value = null;
        }

        if (! empty($value)) {
            // before introducing multiupload values were simple strings
            if (!is_array($value)) {
                $this->value[] = $value;
            } else {
                $this->value = $value;
            }
        }
        $this->setPreviousValue($this->value);
    }

    /**
     * @return array
     */
    public function getPreviousValue(): array
    {
        return json_decode($this->previousValue, true) ?? [];
    }

    /**
     * @param array|null $value
     * @return void
     */
    public function setPreviousValue($value): void
    {
        $this->previousValue = json_encode($value);
    }

    /**
     * Remove old file before resetting the value, useful when clearing the form after submit
     *
     * @param string $formPath
     * @return void
     */
    public function clearValue(string $formPath): void
    {
        $this->deleteFiles();

        $this->setValue(null);
    }

    /**
     * Get allowed extensions for this upload
     *
     * @return string
     */
    public function getAllowedExtensionsAsString(): string
    {
        return strtolower(
            $this->getConfig()['validation']['fileext'] ?? ''
        );
    }

    /**
     * Get allowed extensions for this upload (as array)
     *
     * @return array
     */
    public function getAllowedExtensionsAsArray(): array
    {
        $allowedExtensions = explode(',', $this->getAllowedExtensionsAsString());
        // remove possible whitespaces after comma (e.g. "pdf, txt, png")
        foreach ($allowedExtensions as &$allowedExtension) {
            $allowedExtension = trim($allowedExtension);
        }
        return $allowedExtensions;
    }


    /**
     * Get allowed size for this upload
     *
     * @return string
     */
    public function getAllowedSizeAsString(): string
    {
        return strtolower(
            $this->getConfig()['validation']['filesize'] ?? ''
        );
    }

    /**
     * Maximum allowed download size in bytes.
     * Evaluates field config 'filesize' amd PHP values of upload_max_filesize
     * and post_max_size.
     *
     * As this creates a per-field safeguard, form upload can still fail if
     * sum total of multiple fields exceeds limits.
     *
     * @return int
     */
    public function getMaxSize(): int
    {
        $fieldMax = $this->getAllowedSizeAsString();

        if ($fieldMax) {
            return FileHelper::humanToBytes($fieldMax);
        }
        $phpUploadMax = FileHelper::humanToBytes(ini_get('upload_max_filesize'));
        $phpPostMax = FileHelper::humanToBytes(ini_get('post_max_size'));

        return min($phpUploadMax, $phpPostMax);
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function getViewVariables(): array
    {
        return array_merge(
            parent::getViewVariables(),
            [
                'is_uploaded' => $this->hasValue(),
                'uploaded_files' => $this->getUploadedFiles(),
                'allowed_extensions' => $this->getAllowedExtensionsAsArray(),
                'previous_value' => json_encode($this->getPreviousValue()),
                'max_size' => $this->getMaxSize(),
                'max_size_human' => FileHelper::getMaxSizeHuman($this->getMaxSize()),
            ]
        );
    }

    /**
     * Provides original filenames ('name') and actual filesystem names based on field address ('address')
     *
     * @return array
     */
    public function getUploadedFiles(): array
    {
        if (!$this->hasValue()) {
            return [];
        }

        $uploaded = [];
        foreach ($this->value as $key => $filename) {
            if ($this->formIsPersistent) {
                // value and YAML storage contain original names, but upload files are named after field id
                $uploaded[$key]['name'] = $filename;
                $ext = FileHelper::getFileExtension($filename);
                $uploaded[$key]['address'] = $this->getFormElementIdStringified('.') . "_$key.$ext";
            } else {
                // client file name on transient upload
                // hacky: only available on fresh upload or first restore (gets lost after many failed submits)
                $uploaded[$key]['name'] = $this->originalFilenames[$key] ?? $this->getPreviousValue()[$key];
                $uploaded[$key]['address'] = $filename;
            }
        }

        return $uploaded;
    }

    /**
     * Deletes all files before uploading new one(s) or when resetting the form (save disabled)
     *
     * @return void
     */
    public function deleteFiles(): void
    {
        if ($this->formIsPersistent) {
            foreach (new \DirectoryIterator($this->formDirectory) as $fileInfo) {
                if ($fileInfo->isDot() || !str_starts_with($fileInfo->getFilename(), $this->fieldAddress . '_')) {
                    continue;
                }
                if (unlink($fileInfo->getPathname()) === false) {
                    throw new FormException('Could not delete file: ' . $fileInfo->getFilename());
                }
            }
        } else {
            $previousUploads = $this->getPreviousValue();
            foreach ($previousUploads as $upload) {
                $filePath = $this->formDirectory . $upload;
                if (is_file($filePath)) {
                    unlink($filePath);
                } else {
                    throw new FormException("Could not delete file: '$filePath'");
                }
            }
        }
    }

    /**
     * Persist an uploaded file
     *
     * @param $newUpload
     * @return void
     */
    public function saveNewUpload($newUpload): void
    {
        /**
         * @var UploadedFile $file
         */
        foreach ($newUpload as $key => $file) {
            $filename = $this->moveUploadedFile($file, $key);
            // needed for transient uploads (mon-persistent form)
            $this->originalFilenames[$key] = $file->getClientFilename();
            if ($this->formIsPersistent) {
                $value[$key] = $file->getClientFilename();
            } else {
                $value[$key] = $filename;
            }
        }
        $this->setValue($value);
    }

    /**
     * Moves an uploaded file.
     * http://www.slimframework.com/docs/v4/cookbook/uploading-files.html
     *
     * @param UploadedFile $uploadedFile
     * @param int $key
     * @return string path to moved file
     */
    protected function moveUploadedFile(UploadedFile $uploadedFile, int $key): string
    {
        $extension = FileHelper::getFileExtension(
            $uploadedFile->getClientFilename()
        );

        $baseName = $this->getId();
        $parent = $this->getParent();
        while ($parent !== null) {
            $baseName = $parent->getId() . ".$baseName";
            $parent = $parent->getParent();
        }

        // to prevent conflicts, add timestamp when saving is disabled (multi-user form)
        if (!$this->formIsPersistent) {
            $baseName = time() . '_' . $baseName;
        }
        $fileName = sprintf('%s_%s.%0.8s', $baseName, $key, $extension);

        $filePath = $this->formDirectory . $fileName;
        $uploadedFile->moveTo($filePath);

        return $fileName;
    }
}
