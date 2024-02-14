<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

use CosmoCode\Formserver\Helper\FileHelper;

/**
 * Representation of a file upload
 */
class UploadFormElement extends AbstractDynamicFormElement
{
    /**
     * @var array
     */
    protected $previousValue;

    /**
     * Update previous value on every upload.
     * Also used when restoring persisted values.
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        if (! empty($value)) {
            // before introducing multiupload values were simple strings
            if (is_array($value)) {
                $this->value = $value;
            } else {
                $this->value[] = $value;
            }
        } elseif (is_null($value)) {
            $this->value = null;
        }
        $this->setPreviousValue($this->value);
    }

    /**
     * @return array
     */
    public function getPreviousValue()
    {
        return json_decode($this->previousValue) ?? [];
    }

    /**
     * @param array|null $value
     * @return void
     */
    public function setPreviousValue($value)
    {
        $this->previousValue = json_encode($value);
    }

    /**
     * Remove old file before resetting the value, useful when clearing the form after submit
     *
     * @param $formPath
     */
    public function clearValue($formPath)
    {
        if (is_array($this->value)) {
            foreach ($this->value as $value) {
                if (is_file($formPath . $value)) {
                    unlink($formPath . $value);
                }
            }
        }

        $this->setValue(null);
    }

    /**
     * Get allowed extension for this upload
     *
     * @return string
     */
    public function getAllowedExtensionsAsString()
    {
        return strtolower(
            $this->getConfig()['validation']['fileext'] ?? ''
        );
    }

    /**
     * Get allowed extension for this upload (as array)
     *
     * @return array
     */
    public function getAllowedExtensionsAsArray()
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
    public function getAllowedSizeAsString()
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
    public function getMaxSize()
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
    public function getViewVariables()
    {
        return array_merge(
            parent::getViewVariables(),
            [
                'is_uploaded' => $this->hasValue(),
                'allowed_extensions' => $this->getAllowedExtensionsAsArray(),
                'previous_value' => json_encode($this->getPreviousValue()),
                'max_size' => $this->getMaxSize(),
                'max_size_human' => FileHelper::getMaxSizeHuman($this->getMaxSize()),
            ]
        );
    }
}
