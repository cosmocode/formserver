<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

use CosmoCode\Formserver\Service\LangManager;

/**
 * Representation of a signature
 */
class SignatureFormElement extends AbstractDynamicFormElement
{
    /**
     * @inheritDoc
     * @return array
     */
    public function getViewVariables(): array
    {
        return array_merge(
            parent::getViewVariables(),
            [
                'label_signature_delete' => LangManager::getString(
                    'label_signature_delete'
                ),
                'label_signature_replace' => LangManager::getString(
                    'label_signature_replace'
                ),
            ]
        );
    }
}
