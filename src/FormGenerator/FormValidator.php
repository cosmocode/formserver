<?php

namespace CosmoCode\Formserver\FormGenerator;


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
}
