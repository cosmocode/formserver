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
            if ($formElement instanceof FieldsetFormElement
                && $this->fieldsetValidatable($formElement)
            ) {
                foreach ($formElement->getChildren() as $fieldsetChild) {
                    $this->validateFormElement($fieldsetChild);
                }
            } else {
                $this->validateFormElement($formElement);
            }
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
        if ($formElement instanceof  AbstractDynamicFormElement
            && ($formElement->hasValue() || $formElement->isRequired())
        ) {
            $value = $formElement->getValue();
            foreach ($formElement->getValidationRules() as $validation => $allowed) {
                switch ($validation) {
                    case 'required':
                        if (! Validator::notEmpty()->validate($value)) {
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
                                'error_match', $allowed
                            );
                            return;
                        }
                        break;
                    case 'filesize':
                        /**
                         * @var UploadFormElement $formElement
                         */
                        $filePath = $this->form->getFormDirectory() . $value;
                        if (! Validator::size(null, $allowed)->validate($filePath)) {
                            $formElement->addError(
                                'error_filesize', $allowed
                            );
                            $this->dropFile($formElement);
                        }
                        break;
                    case 'fileext':
                        /**
                         * @var UploadFormElement $formElement
                         */
                        $validators = [];
                        foreach (
                            $formElement->getAllowedExtensionsAsArray() as $ext
                        ) {
                            $validators[] = Validator::extension(trim($ext));
                        }
                        if (! Validator::oneOf($validators)->validate($value)) {
                            $formElement->addError(
                                'error_fileext',
                                $formElement->getAllowedExtensionsAsString()
                            );
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
     * @return void
     */
    protected function dropFile(UploadFormElement $formElement)
    {
        $filePath = $this->form->getFormDirectory() . $formElement->getValue();
        unlink($filePath);
        $formElement->setValue(null);
    }

    /**
     * Check if fieldset should be validated.
     * If it is not visiable (toggle condition not matching) then it
     * should not be validated.
     *
     * @param FieldsetFormElement $formElement
     * @return bool
     */
    protected function fieldsetValidatable(FieldsetFormElement $formElement)
    {
        if ($formElement->hasToggle()) {
            $toggleValue = $this->form->getFormElementValue(
                $formElement->getToggleFieldId()
            );
            $requiredToggleValue = $formElement->getToggleValue();

            return $toggleValue === $requiredToggleValue;
        }

        return true;
    }
}
