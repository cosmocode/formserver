<?php

namespace CosmoCode\Formserver\FormGenerator;


use CosmoCode\Formserver\Exceptions\FormException;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\InputFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\UploadFormElement;
use CosmoCode\Formserver\Helper\YamlHelper;
use Slim\Psr7\UploadedFile;

class Form
{
    /**
     * @var string
     */
    protected $formDirectory;

    /**
     * @var array
     */
    protected $meta;

    /** @var AbstractFormElement[] */
    protected $formElements = [];

    public function __construct(string $formId)
    {
        $this->formDirectory = __DIR__ . "/../../data/$formId/";
        $config = YamlHelper::parseYaml($this->formDirectory . 'config.yaml');
        $this->meta = $config['meta'] ?? [];

        foreach ($config['form'] as $formElementId => $formElementConfig) {
            $this->formElements[] = FormElementFactory::createFormElement($formElementId, $formElementConfig);
        }
    }

    public function getFormElements()
    {
        return $this->formElements;
    }

    public function getMeta(string $key)
    {
        return $this->meta[$key] ?? null;
    }

    public function submit(array $data, array $files)
    {
        // Important! Restore persisted data first to determine if UploadFormElements have already an uploaded file
        // (They have a value which containts the file name)
        $this->restore();

        foreach ($this->formElements as $formElement) {
            if ($formElement instanceof FieldsetFormElement) {
                foreach ($formElement->getChildren() as $fieldsetChild) {
                    $this->submitFormElement($fieldsetChild, $data, $files);
                }
            } else {
                $this->submitFormElement($formElement, $data, $files);
            }
        }
    }

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

        if (!empty($values)) {
            YamlHelper::persistYaml($values, $this->formDirectory . 'values.yaml');
        }
    }

    public function restore()
    {
        $values = YamlHelper::parseYaml($this->formDirectory . 'values.yaml');

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

    protected function submitFormElement(AbstractFormElement $formElement, array $data, array $files)
    {
        if ($formElement instanceof UploadFormElement) {
            /** @var UploadedFile $file */
            $file = $this->getFormElementValueFromArray($formElement, $files);

            if ($file !== null && $file->getError() === UPLOAD_ERR_OK) {
                if (!empty($formElement->getValue())) {
                    $this->deleteFileFromFormElement($formElement);
                }
                $fileName = $this->moveUploadedFile($file, $formElement);
                $formElement->setValue($fileName);
            }
        } elseif ($formElement instanceof InputFormElement) {
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
    protected function moveUploadedFile(UploadedFile $uploadedFile, UploadFormElement $formElement)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $baseName = $formElement->hasParent()
            ? $formElement->getParent()->getId() . '_' . $formElement->getId()
            : $formElement->getId();
        $fileName = sprintf('%s.%0.8s', $baseName, $extension);

        $filePath = $this->formDirectory . $fileName;
        $uploadedFile->moveTo($filePath);

        return $fileName;
    }

    /**
     * Deletes a file (in favor of another uploaded one)
     *
     * @param UploadFormElement $formElement
     */
    protected function deleteFileFromFormElement(UploadFormElement $formElement) {
        $filePath = $this->formDirectory . $formElement->getValue();
        if(is_file($filePath)) {
            unlink($filePath);
        } else {
            throw new FormException("Could not delete file: '$filePath'");
        }
    }

    protected function restoreValue(array $values, AbstractFormElement $formElement)
    {
        if ($formElement instanceof InputFormElement || $formElement instanceof UploadFormElement) {
            $value = $this->getFormElementValueFromArray($formElement, $values);
            if (!empty($value)) {
                $formElement->setValue($value);
            }
        }
    }

    protected function getFormElementValueFromArray(AbstractFormElement $formElement, array $array) {
        return $formElement->hasParent()
            ? $array[$formElement->getParent()->getId()][$formElement->getId()] ?? null
            : $array[$formElement->getId()] ?? null;
    }

    protected function insertFormElementValueInArray(AbstractFormElement $formElement, array &$array) {
        if ($formElement instanceof InputFormElement || $formElement instanceof UploadFormElement) {
            // Dont need to persist an empty value
            if (empty($formElement->getValue())) {
                return;
            }

            if ($formElement->hasParent()) {
                $array[$formElement->getParent()->getId()][$formElement->getId()] = $formElement->getValue();
            } else {
                $array[$formElement->getId()] = $formElement->getValue();
            }
        }
    }
}
