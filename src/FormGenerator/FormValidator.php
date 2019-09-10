<?php

namespace CosmoCode\Formserver\FormGenerator;


use CosmoCode\Formserver\FormGenerator\FormElements\AbstractDynamicFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\UploadFormElement;
use Respect\Validation\Validator;

class FormValidator
{
    /**
     * @var Form
     */
    protected $form;

    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    public function validate() {
        foreach ($this->form->getFormElements() as $formElement) {
            if ($formElement instanceof FieldsetFormElement) {
                foreach ($formElement->getChildren() as $fieldsetChild) {
                    $this->validateFormElement($fieldsetChild);
                }
            } else {
                $this->validateFormElement($formElement);
            }
        }
    }

    /**
     * @param AbstractFormElement $formElement
     */
    protected function validateFormElement(AbstractFormElement $formElement) {
        if (
            $formElement instanceof  AbstractDynamicFormElement &&
            ($formElement->hasValue() || $formElement->isRequired())
        ) {
            $value = $formElement->getValue();
            foreach ($formElement->getValidationRules() as $validation => $allowed) {
                switch ($validation) {
                    case 'required':
                        if (!Validator::notEmpty()->validate($value)) {
                            $formElement->addError('value is required');
                            return;
                        }
                        break;
                    case 'min':
                        if (!Validator::intVal()->min($allowed)->validate($value)) {
                            $formElement->addError('value smaller than ' . $allowed);
                            return;
                        }
                        break;
                    case 'max':
                        if (!Validator::intVal()->max($allowed)->validate($value)) {
                            $formElement->addError('value larger than ' . $allowed);
                            return;
                        }
                        break;
                    case 'match':
                        if (!Validator::regex($allowed)->validate($value)) {
                            $formElement->addError('value does not match ' . $allowed);
                            return;
                        }
                        break;
                    case 'filesize':
                        /** @var UploadFormElement $formElement */
                        $filePath = $this->form->getFormDirectory() . $value;
                        if (!Validator::size(null, $allowed)->validate($filePath)) {
                            $formElement->addError('Fehler: erlaubte Dateigröße ' . $allowed);
                            $this->dropFile($formElement);
                        }

                        break;
                    case 'fileext':
                        /** @var UploadFormElement $formElement */
                        $validators = [];
                        foreach ($formElement->getAllowedExtensionsAsArray() as $ext) {
                            $validators[] = Validator::extension(trim($ext));
                        }
                        if (!Validator::oneOf($validators)->validate($value)) {
                            $formElement->addError('Fehler: erlaubte Formate ' . $formElement->getAllowedExtensionsAsString());
                            $this->dropFile($formElement);

                        }

                        break;
                }
            }
        }
    }

    /**
     * Removes a file form an UploadFormElement
     *
     * @param UploadFormElement $formElement
     */
    protected function dropFile(UploadFormElement $formElement) {
        $filePath = $this->form->getFormDirectory() . $formElement->getValue();
        unlink($filePath);
        $formElement->setValue(null);
    }
}