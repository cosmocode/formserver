<?php

namespace CosmoCode\Formserver\FormGenerator;

use CosmoCode\Formserver\Exceptions\FormException;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\ChecklistFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\DateFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\DateTimeFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\DownloadFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\DropdownFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\EmailFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\HiddenFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\HrFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\ImageFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\MarkDownFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\NumberInputFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\RadiosetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\SignatureFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\SpacerFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\TextAreaFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\TextInputFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\TimeFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\UploadFormElement;

/**
 * Factory to create form elements
 *
 * @package CosmoCode\Formserver\FormGenerator
 */
class FormElementFactory
{
    /**
     * Static factory function to create a form element
     *
     * @param string $id
     * @param array $config
     * @param FieldsetFormElement|null $parent
     * @param string $formId
     * @param bool $formIsPersistent
     * @return AbstractFormElement
     */
    public static function createFormElement(
        string $id,
        array $config,
        FieldsetFormElement $parent = null,
        string $formId = '',
        bool $formIsPersistent = false
    ): AbstractFormElement {
        $formType = $config['type'];
        switch ($formType) {
            case 'fieldset':
                return self::createFieldsetFormElement($id, $config, $parent, $formId, $formIsPersistent);
            case 'markdown':
                return new MarkDownFormElement($id, $config, $parent, $formId);
            case 'hidden':
                return new HiddenFormElement($id, $config, $parent);
            case 'download':
                return new DownloadFormElement($id, $config, $parent, $formId);
            case 'image':
                return new ImageFormElement($id, $config, $parent, $formId);
            case 'hr':
                return new HrFormElement($id, $config, $parent);
            case 'upload':
                return new UploadFormElement($id, $config, $parent, $formId, $formIsPersistent);
            case 'textinput':
                return new TextInputFormElement($id, $config, $parent, $formId);
            case 'numberinput':
                return new NumberInputFormElement($id, $config, $parent, $formId);
            case 'date':
                return new DateFormElement($id, $config, $parent, $formId);
            case 'time':
                return new TimeFormElement($id, $config, $parent, $formId);
            case 'datetime':
                return new DateTimeFormElement($id, $config, $parent, $formId);
            case 'email':
                return new EmailFormElement($id, $config, $parent, $formId);
            case 'textarea':
                return new TextAreaFormElement($id, $config, $parent, $formId);
            case 'radioset':
                return new RadiosetFormElement($id, $config, $parent, $formId);
            case 'checklist':
                return new ChecklistFormElement($id, $config, $parent, $formId);
            case 'dropdown':
                return new DropdownFormElement($id, $config, $parent, $formId);
            case 'signature':
                return new SignatureFormElement($id, $config, $parent, $formId);
            case 'spacer':
                return new SpacerFormElement($id, $config, $parent);
            default:
                throw new FormException(
                    "Could not build FormElement id:$id. Undefined type ($formType)"
                );
        }
    }

    /**
     * Helper function to create a fieldset form element
     *
     * @param string $id
     * @param array $config
     * @param FieldsetFormElement|null $parent
     * @param string $formId
     * @param bool $formIsPersistent
     * @return FieldsetFormElement
     */
    protected static function createFieldsetFormElement(
        string $id,
        array $config,
        FieldsetFormElement $parent = null,
        string $formId = '',
        bool $formIsPersistent = false
    ): FieldsetFormElement {
        $fieldsetFormElement = new FieldsetFormElement($id, $config, $parent, $formId);

        foreach ($config['children'] as $childId => $childConfig) {
            $childFormElement = self::createFormElement(
                $childId,
                $childConfig,
                $fieldsetFormElement,
                $formId,
                $formIsPersistent
            );

            $fieldsetFormElement->addChild($childFormElement);
        }

        return $fieldsetFormElement;
    }
}
