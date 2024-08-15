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
    public const COLORS_BULMA = [
        'white',
        'black',
        'light',
        'dark',
        'primary',
        'link',
        'info',
        'success',
        'warning',
        'danger',
        'black-bis',
        'black-ter',
        'grey-darker',
        'grey-dark',
        'grey',
        'grey-light',
        'grey-lighter',
        'white-ter',
        'white-bis'
    ];

    // phpcs:ignore
    const COLORS_REGEX = '/^(#[0-9a-fA-F]{3,4}|#[0-9a-fA-F]{6}|#[0-9a-fA-F]{8}|rgb\(\s*\d*(?:\.\d*)?\s*,\s*\d*(?:\.\d*)?\s*,\s*\d*(?:\.\d*)?\s*\)|rgba\(\s*(?:\d*(?:\.\d*)?%|0)\s*,\s*(?:\d*(?:\.\d*)?%|0)\s*,\s*(?:\d*(?:\.\d*)?%|0)\s*,\s*\d*(?:\.\d*)?%?\s*\)|rgba?\(\s*\d*(?:\.\d*)?\s*\d*(?:\.\d*)?\s*\d*(?:\.\d*)?\s*(?:\/\s*\d*(?:\.\d*)?%?\s*)?\)|rgba?\(\s*(?:\d*(?:\.\d*)?%|0)\s*(?:\d*(?:\.\d*)?%|0)\s*(?:\d*(?:\.\d*)?%|0)\s*(?:\/\s*\d*(?:\.\d*)?%?\s*)?\)|hsl\(\s*\d*(?:\.\d*)?(?:deg|grad|rad|turn)?\s*,\d*(?:\.\d*)?%\s*,\s*\d*(?:\.\d*)?%\s*\)|hsla\(\s*\d*(?:\.\d*)?(?:deg|grad|rad|turn)?\s*,(?:\d*(?:\.\d*)?%|0)\s*,\s*(?:\d*(?:\.\d*)?%|0)\s*,\s*\d*(?:\.\d*)?%?\s*\)|h(?:sla?|wb)\(\s*\d*(?:\.\d*)?(?:deg|grad|rad|turn)?\s*\d*(?:\.\d*)?\s*\d*(?:\.\d*)?\s*(?:\/\s*\d*(?:\.\d*)?\s*)?\)|h(?:sla?|wb)\(\s*\d*(?:\.\d*)?(?:deg|grad|rad|turn)?\s*(?:\d*(?:\.\d*)?%|0)\s*(?:\d*(?:\.\d*)?%|0)\s*(?:\/\s*\d*(?:\.\d*)?%?\s*)?\))$/';

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
        if (
            $formElement instanceof FieldsetFormElement
            && ! $formElement->isDisabled()
        ) {
            foreach ($formElement->getChildren() as $fieldsetChild) {
                $this->validateFormElement($fieldsetChild);
            }
        } elseif (
            $formElement instanceof  AbstractDynamicFormElement
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
                        foreach (
                            $formElement->getAllowedExtensionsAsArray() as $ext
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
