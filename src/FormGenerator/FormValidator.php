<?php

namespace CosmoCode\Formserver\FormGenerator;

use CosmoCode\Formserver\FormGenerator\FormElements\AbstractDynamicFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\UploadFormElement;
use Respect\Validation\Validator;

/**
 * Validates all form elements of a form
 *
 * @package CosmoCode\Formserver\FormGenerator
 */
class FormValidator
{
    /**
     * @var Form
     */
    protected $form;

    /**
     * Injects the form
     *
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    /**
     * Validates all form elements
     *
     * @return void
     */
    public function validate()
    {
        foreach ($this->form->getFormElements() as $formElement) {
            $this->validateFormElement($formElement);
        }
    }

    /**
     * Helper function to validate a form element
     *
     * @param AbstractFormElement $formElement
     * @return void
     */
    protected function validateFormElement(AbstractFormElement $formElement)
    {
        if ($formElement instanceof FieldsetFormElement
            && ! $formElement->isDisabled()
        ) {
            foreach ($formElement->getChildren() as $fieldsetChild) {
                $this->validateFormElement($fieldsetChild);
            }
        } elseif ($formElement instanceof  AbstractDynamicFormElement
            && ($formElement->hasValue() || $formElement->isRequired())
        ) {
            $value = $formElement->getValue();
            foreach ($formElement->getValidationRules() as $validation => $allowed) {
                switch ($validation) {
                    case 'required':
                        if (is_null($value)) {
                            $formElement->addError('error_required');
                            return;
                        }
                        break;
                    case 'min':
                        if (! Validator::intVal()->min($allowed)->validate($value)) {
                            $formElement->addError('error_min', $allowed);
                            return;
                        }
                        break;
                    case 'max':
                        if (! Validator::intVal()->max($allowed)->validate($value)) {
                            $formElement->addError('error_max', $allowed);
                            return;
                        }
                        break;
                    case 'match':
                        if (! Validator::regex($allowed)->validate($value)) {
                            $formElement->addError(
                                'error_match',
                                $allowed
                            );
                            return;
                        }
                        break;
                    case 'filesize':
                        /**
                         * @var UploadFormElement $formElement
                         */
                        foreach ($value as $key => $file) {
                            $filePath = $this->form->getFormDirectory() . $file;
                            if (! Validator::size(null, $allowed)->validate($filePath)) {
                                $formElement->addError(
                                    'error_filesize',
                                    $allowed
                                );
                                $this->dropFile($formElement, $key);
                            }
                        }
                        break;
                    case 'fileext':
                        /**
                         * @var UploadFormElement $formElement
                         */
                        $validators = [];
                        foreach ($formElement->getAllowedExtensionsAsArray() as $ext
                        ) {
                            $validators[] = Validator::extension(trim($ext));
                        }
                        foreach ($value as $key => $file) {
                            if (! Validator::oneOf($validators)->validate($file)) {
                                $formElement->addError(
                                    'error_fileext',
                                    $formElement->getAllowedExtensionsAsString()
                                );
                                $this->dropFile($formElement, $key);
                            }
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
     * @param int $key
     * @return void
     */
    protected function dropFile(UploadFormElement $formElement, int $key)
    {
        $files = $formElement->getValue();

        $filePath = $this->form->getFormDirectory() . $files[$key];
        is_file($filePath) && unlink($filePath);
        $formElement->setValue(null);
    }
}
