<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

use CosmoCode\Formserver\Service\LangManager;

/**
 * Representation of a signature
 */
class SignatureFormElement extends AbstractDynamicFormElement
{
    /**
     * If the signature SVG consists of an empty root note,
     * it should be interpreted as having no value.
     *
     * @return string|null
     */
    public function getValue()
    {
        if ($this->hasValue()) {
            $encodedImage = explode(",", $this->value)[1];
            $decodedImage = base64_decode($encodedImage);
            $xml = new \SimpleXMLElement($decodedImage);
            return $xml->children()->count() ? $this->value : null;
        }
        return null;
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
                'label_signature_delete' => LangManager::getString(
                    'label_signature_delete'
                )
            ]
        );
    }
}
