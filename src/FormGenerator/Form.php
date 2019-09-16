<?php

namespace CosmoCode\Formserver\FormGenerator;


use CosmoCode\Formserver\Exceptions\FormException;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractDynamicFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\UploadFormElement;
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
        $fieldPath = explode('.', $fieldId);
        $rootElementId = array_shift($fieldPath);

        foreach ($this->formElements as $formElement) {
            if ($formElement->getId() === $rootElementId) {
                if ($formElement instanceof FieldsetFormElement) {
                    $childElementId = array_shift($fieldPath);
                    foreach ($formElement->getChildren() as $fieldsetChild) {
                        if ($fieldsetChild instanceof AbstractDynamicFormElement
                            && $fieldsetChild->getId() === $childElementId
                        ) {
                            return $fieldsetChild->getValue();
                        }
                    }
                } elseif ($formElement instanceof AbstractDynamicFormElement) {
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
            if ($formElement instanceof FieldsetFormElement) {
                foreach ($formElement->getChildren() as $fieldsetChild) {
                    $this->submitFormElement($fieldsetChild, $data, $files);
                }
            } else {
                $this->submitFormElement($formElement, $data, $files);
            }
        }

        // en-/disable fieldsets depending on toggle value
        foreach ($this->formElements as $formElement) {
            if ($formElement instanceof FieldsetFormElement
                && $formElement->hasToggle()
            ) {
                $toggleValue = $this->getFormElementValue(
                    $formElement->getToggleFieldId()
                );
                $requiredToggleValue = $formElement->getToggleValue();

                $formElement->setDisabled(
                    $toggleValue !== $requiredToggleValue
                );
            }
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
            if ($formElement instanceof FieldsetFormElement) {
                foreach ($formElement->getChildren() as $fieldsetChild) {
                    $this->insertFormElementValueInArray($fieldsetChild, $values);
                }
            } else {
                $this->insertFormElementValueInArray($formElement, $values);
            }
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
        $values = YamlHelper::parseYaml($this->getFormDirectory() . 'values.yaml');

        foreach ($this->formElements as $formElement) {
            if ($formElement instanceof FieldsetFormElement) {
                foreach ($formElement->getChildren() as $fieldsetChild) {
                    $this->restoreValue($values, $fieldsetChild);
                }
            } else {
                $this->restoreValue($values, $formElement);
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
            if ($formElement instanceof FieldsetFormElement && ! $formElement->isDisabled()) {
                foreach ($formElement->getChildren() as $fieldsetChild) {
                    if ($fieldsetChild instanceof AbstractDynamicFormElement
                        && ! $fieldsetChild->isValid()
                    ) {
                        return false;
                    }
                }
            } elseif ($formElement instanceof AbstractDynamicFormElement
                && ! $formElement->isValid()
            ) {
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
        if ($formElement instanceof UploadFormElement) {
            /**
             * @var UploadedFile $file
             */
            $file = $this->getFormElementValueFromArray($formElement, $files);

            if ($file !== null && $file->getError() === UPLOAD_ERR_OK) {
                if (! empty($formElement->getValue())) {
                    $this->deleteFileFromFormElement($formElement);
                }
                $fileName = $this->moveUploadedFile($file, $formElement);
                $formElement->setValue($fileName);
            }
        } elseif ($formElement instanceof AbstractDynamicFormElement) {
            $value = $this->getFormElementValueFromArray($formElement, $data);
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
        $extension = strtolower(
            pathinfo(
                $uploadedFile->getClientFilename(),
                PATHINFO_EXTENSION
            )
        );

        $baseName = $formElement->hasParent()
            ? $formElement->getParent()->getId() . '_' . $formElement->getId()
            : $formElement->getId();
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
     * Helper function to restore a specific value to a form element
     *
     * @param array $values
     * @param AbstractFormElement $formElement
     * @return void
     */
    protected function restoreValue(array $values, AbstractFormElement $formElement)
    {
        if ($formElement instanceof AbstractDynamicFormElement) {
            $value = $this->getFormElementValueFromArray($formElement, $values);
                $formElement->setValue($value);
        }
    }

    /**
     * Helper function to get the value of a form element from provided array
     * This can be used for $_FILES and $_POST as they have the same structure
     *
     * @param AbstractFormElement $formElement
     * @param array $array
     * @return mixed|null
     */
    protected function getFormElementValueFromArray(
        AbstractFormElement $formElement,
        array $array
    ) {
        $formElementId = $formElement->getId();
        return $formElement->hasParent()
            ? $array[$formElement->getParent()->getId()][$formElementId] ?? null
            : $array[$formElementId] ?? null;
    }

    /**
     * Helper function  to fill a array with values from form element
     * The given array parameter is a pointer
     *
     * @param AbstractFormElement $formElement
     * @param array $array
     * @return void
     */
    protected function insertFormElementValueInArray(
        AbstractFormElement $formElement,
        array &$array
    ) {
        if ($formElement instanceof AbstractDynamicFormElement
            || $formElement instanceof UploadFormElement
        ) {
            // Dont need to persist an empty value
            if (empty($formElement->getValue())) {
                return;
            }

            if ($formElement->hasParent()) {
                $array[$formElement->getParent()->getId()][$formElement->getId()]
                    = $formElement->getValue();
            } else {
                $array[$formElement->getId()] = $formElement->getValue();
            }
        }
    }
}
