<?php

namespace CosmoCode\Formserver\FormGenerator;

use CosmoCode\Formserver\Exceptions\FormException;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractDynamicFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\ChecklistFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\DropdownFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\UploadFormElement;
use CosmoCode\Formserver\Helper\FileHelper;
use CosmoCode\Formserver\Helper\YamlHelper;
use Slim\Psr7\UploadedFile;

/**
 * Contains the form and provides basic functionality
 *
 * @package CosmoCode\Formserver\FormGenerator
 */
class Form
{
    const DATA_DIR = __DIR__ . '/../../data/';

    const MODE_SHOW = 'show';
    const MODE_SAVE = 'save';
    const MODE_SEND = 'send';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $meta;

    /**
     * @var AbstractFormElement[]
     */
    protected $formElements = [];

    /**
     * @var string
     */
    protected $mode = self::MODE_SHOW;

    /**
     * Build a form from YAML
     *
     * @param string $formId
     */
    public function __construct(string $formId)
    {
        $this->id = $formId;
        $config = YamlHelper::parseYaml($this->getFormDirectory() . 'config.yaml');
        $this->meta = $config['meta'] ?? [];

        foreach ($config['form'] as $formElementId => $formElementConfig) {
            $this->formElements[] = FormElementFactory::createFormElement(
                $formElementId,
                $formElementConfig
            );
        }
    }

    /**
     * Returns all form elements
     *
     * @return AbstractFormElement[]
     */
    public function getFormElements()
    {
        return $this->formElements;
    }

    /**
     * Returns the value of a form element
     *
     * @param string $fieldId
     * @return array
     */
    public function getFormElementValue(string $fieldId)
    {
        $formElementIdPath = explode('.', $fieldId);
        $rootElementId = array_shift($formElementIdPath);

        foreach ($this->formElements as $formElement) {
            if ($formElement->getId() === $rootElementId) {
                foreach ($formElementIdPath as $formId) {
                    if ($formElement instanceof FieldsetFormElement) {
                        $formElement = $formElement->getChild($formId);
                    } else {
                        throw new FormException(
                            "Cant get value of $fieldId. It does not exist."
                        );
                    }
                }

                if ($formElement instanceof AbstractDynamicFormElement) {
                    return $formElement->getValue();
                }
            }
        }

        throw new FormException("Cant get value of $fieldId. It does not exist.");
    }

    /**
     * Get the meta config
     *
     * @param string $key
     * @return mixed|null
     */
    public function getMeta(string $key)
    {
        return $this->meta[$key] ?? null;
    }

    /**
     * Get the directory path of current form
     *
     * @return string
     */
    public function getFormDirectory()
    {
        return self::DATA_DIR . $this->id . '/';
    }

    /**
     * Get the id of current form
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the current mode (user intend)
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Submit data to the form
     *
     * @param array $data $_POST
     * @param array $files $_FILES
     * @return void
     */
    public function submit(array $data, array $files)
    {
        // Important! Restore persisted data first to determine if UploadFormElements
        // have already an uploaded file (They have a value which containts the file
        // name)
        $this->restore();

        // submit data
        foreach ($this->formElements as $formElement) {
            $this->submitFormElement($formElement, $data, $files);
        }

        // En-/disable fieldsets depending on toggle value
        // This must happen after all POST data was submitted
        foreach ($this->formElements as $formElement) {
            $this->toggleFieldsets($formElement);
        }

        $this->setMode($data);
    }

    /**
     * Persist all submitted data to YAML
     *
     * @return void
     */
    public function persist()
    {
        $values = [];
        foreach ($this->formElements as $formElement) {
            $this->injectValueToArray($values, $formElement);
        }

        if (! empty($values)) {
            YamlHelper::persistYaml(
                $values,
                $this->getFormDirectory() . 'values.yaml'
            );
        }
    }

    /**
     * Restore all persisted data
     *
     * @return void
     */
    public function restore()
    {
        // Form was saved before. Restore data
        if (is_file($this->getFormDirectory() . 'values.yaml')) {
            $values = YamlHelper::parseYaml(
                $this->getFormDirectory() . 'values.yaml'
            );

            foreach ($this->formElements as $formElement) {
                $this->restoreValue($values, $formElement);
            }
        } else {
            // Form was never saved before. Set default values
            foreach ($this->formElements as $formElement) {
                $this->setDefaultValues($formElement);
            }
        }
    }

    /**
     * Returns boolean if all form elements of this form are valid
     *
     * @return bool
     */
    public function isValid()
    {
        foreach ($this->formElements as $formElement) {
            if (! $this->isFormElementValid($formElement)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Helper function to submit data to a form element
     *
     * @param AbstractFormElement $formElement
     * @param array $data
     * @param array $files
     * @return void
     */
    protected function submitFormElement(
        AbstractFormElement $formElement,
        array $data,
        array $files
    ) {
        if ($formElement instanceof FieldsetFormElement) {
            foreach ($formElement->getChildren() as $fieldsetChild) {
                $subData = $data[$formElement->getId()] ?? [];
                $subFiles = $files[$formElement->getId()] ?? [];
                $this->submitFormElement($fieldsetChild, $subData, $subFiles);
            }
        } elseif ($formElement instanceof UploadFormElement) {
            /**
             * @var UploadedFile $file
             */
            $file = $files[$formElement->getId()] ?? null;

            if ($file !== null && $file->getError() === UPLOAD_ERR_OK) {
                if (! empty($formElement->getValue())) {
                    // Reupload delete old file first
                    $this->deleteFileFromFormElement($formElement);
                }
                $fileName = $this->moveUploadedFile($file, $formElement);
                $formElement->setValue($fileName);
            }
        } elseif ($formElement instanceof AbstractDynamicFormElement) {
            $value = $data[$formElement->getId()] ?? null;
            // Important! Value must be set, even if empty. User can unset fields
            $formElement->setValue($value);
        }
    }

    /**
     * Moves an uploaded file.
     * http://www.slimframework.com/docs/v3/cookbook/uploading-files.html
     *
     * @param UploadedFile $uploadedFile
     * @param UploadFormElement $formElement
     * @return string
     */
    protected function moveUploadedFile(
        UploadedFile $uploadedFile,
        UploadFormElement $formElement
    ) {
        $extension = FileHelper::getFileExtension(
            $uploadedFile->getClientFilename()
        );

        $baseName = $formElement->getId();
        $parent = $formElement->getParent();
        while ($parent !== null) {
            $baseName = $parent->getId() . ".$baseName";
            $parent = $parent->getParent();
        }

        $fileName = sprintf('%s.%0.8s', $baseName, $extension);

        $filePath = $this->getFormDirectory() . $fileName;
        $uploadedFile->moveTo($filePath);

        return $fileName;
    }

    /**
     * Deletes a file (in favor of another uploaded one)
     *
     * @param UploadFormElement $formElement
     * @return void
     */
    protected function deleteFileFromFormElement(UploadFormElement $formElement)
    {
        $filePath = $this->getFormDirectory() . $formElement->getValue();
        if (is_file($filePath)) {
            unlink($filePath);
        } else {
            throw new FormException("Could not delete file: '$filePath'");
        }
    }

    /**
     * Helper function to en-/disable fieldsets depending on toggle value
     *
     * @param AbstractFormElement $formElement
     * @return void
     */
    protected function toggleFieldsets(
        AbstractFormElement $formElement
    ) {
        if ($formElement instanceof FieldsetFormElement) {
            if ($formElement->hasToggle()) {
                $submittedValue = $this->getFormElementValue(
                    $formElement->getToggleFieldId()
                );
                $toggleValue = $formElement->getToggleValue();

                // Checklist can have multiple values
                if (is_array($submittedValue)) {
                    $disabled = ! in_array($toggleValue, $submittedValue);
                } else {
                    $disabled = $submittedValue !== $toggleValue;
                }

                $formElement->setDisabled($disabled);
            }

            if (! $formElement->isDisabled()) {
                foreach ($formElement->getChildren() as $fieldsetChild) {
                    $this->toggleFieldsets($fieldsetChild);
                }
            }
        }
    }

    /**
     * Sets the current mode
     *
     * @param array $data
     * @return void
     */
    protected function setMode(array $data)
    {
        if (isset($data['formcontrol']['send'])) {
            $this->mode = self::MODE_SEND;
        } elseif (isset($data['formcontrol']['save'])) {
            $this->mode = self::MODE_SAVE;
        } else {
            $this->mode = self::MODE_SHOW;
        }
    }

    /**
     * Helper function to restore a specific value to a form element
     *
     * @param array $values
     * @param AbstractFormElement $formElement
     * @return void
     */
    protected function restoreValue(array $values, AbstractFormElement $formElement)
    {
        if ($formElement instanceof FieldsetFormElement) {
            $subValues = $values[$formElement->getId()] ?? [];
            foreach ($formElement->getChildren() as $fieldsetChild) {
                $this->restoreValue($subValues, $fieldsetChild);
            }
        } elseif ($formElement instanceof AbstractDynamicFormElement) {
            $value = $values[$formElement->getId()] ?? null;
            $formElement->setValue($value);
        }
    }

    /**
     * Helper function to restore a specific value to a form element
     *
     * @param AbstractFormElement $formElement
     * @return void
     */
    protected function setDefaultValues(AbstractFormElement $formElement)
    {
        if ($formElement instanceof FieldsetFormElement) {
            foreach ($formElement->getChildren() as $fieldsetChild) {
                $this->setDefaultValues($fieldsetChild);
            }
        } elseif ($formElement instanceof ChecklistFormElement || $formElement instanceof  DropdownFormElement) {
            $formElement->setDefaultValue();
        }
    }

    /**
     * Helper function to restore a specific value to a form element
     *
     * @param array $values
     * @param AbstractFormElement $formElement
     * @return void
     */
    protected function injectValueToArray(
        array &$values,
        AbstractFormElement $formElement
    ) {
        if ($formElement instanceof FieldsetFormElement) {
            $tempValues = [];
            foreach ($formElement->getChildren() as $fieldsetChild) {
                $this->injectValueToArray($tempValues, $fieldsetChild);
            }
            $values[$formElement->getId()] = $tempValues;
        } elseif ($formElement instanceof AbstractDynamicFormElement
            && $formElement->hasValue()
        ) {
            $values[$formElement->getId()] = $formElement->getValue();
        }
    }

    /**
     * Helper function to check form elements recursively if they are valid
     *
     * @param AbstractFormElement $formElement
     * @return bool
     */
    protected function isFormElementValid(AbstractFormElement $formElement)
    {
        if ($formElement instanceof FieldsetFormElement
            && ! $formElement->isDisabled()
        ) {
            foreach ($formElement->getChildren() as $fieldsetChild) {
                if (! $this->isFormElementValid($fieldsetChild)) {
                    return false;
                }
            }
        } elseif ($formElement instanceof AbstractDynamicFormElement) {
            return $formElement->isValid();
        }

        // AbstractStaticFormElements dont have an input they are always valid
        return true;
    }
}
