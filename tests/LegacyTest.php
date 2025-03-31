<?php

namespace CosmoCode\Formserver\Tests;

use PHPUnit\Framework\TestCase;

class LegacyTest extends TestCase
{
    public function testTransformFlat(): void
    {
        $elements = [
            'type' => 'fieldset',
            'label' => 'Dynamic elements',
            'column' => 'is-half',
            'toggle' => [
                'field' => 'fieldset0.radioset1',
                'value' => 'Dynamic elements',
            ],
            'children' => [
                'numberinput1' => [
                    'type' => 'numberinput',
                    'label' => 'Numberinput',
                ],
            ],
        ];

        $expected = [
            'type' => 'fieldset',
            'label' => 'Dynamic elements',
            'column' => 'is-half',
            'visible' => 'fieldset0.radioset1 == \'Dynamic elements\'',
            'children' => [
                'numberinput1' => [
                    'type' => 'numberinput',
                    'label' => 'Numberinput',
                ],
            ],
        ];

        $actual = \CosmoCode\Formserver\Helper\LegacyHelper::transform($elements);

        $this->assertSame($expected, $actual);
    }

    public function testTransform(): void
    {
        $elements = [
            'fieldset0' => [
                'type' => 'fieldset',
                'column' => 'is-full',
                'children' => [
                    'radioset1' => [
                        'type' => 'radioset',
                        'label' => 'Toggle different fieldsets (hidden fieldsets lose all their values when the form is submitted/saved!)',
                        'column' => 'is-half',
                        'alignment' => 'vertical',
                        'validation' => [
                            'required' => false,
                        ],
                        'choices' => [
                            0 => 'Static elements',
                            1 => 'Dynamic elements',
                        ],
                    ],
                    'fieldset_static' => [
                        'type' => 'fieldset',
                        'label' => 'Static form elements',
                        'column' => 'is-half',
                        'toggle' => [
                            'field' => 'fieldset0.radioset1',
                            'value' => 'Static elements',
                        ],
                        'children' => [
                            'markdown0' => [
                                'type' => 'markdown',
                                'label' => 'markdown',
                                'markdown' => 'Hello _Markdown_!',
                            ],
                            'image0' => [
                                'type' => 'image',
                                'label' => 'Image',
                                'src' => 'logo.png',
                            ],
                            'download0' => [
                                'type' => 'download',
                                'label' => 'Download logo.png from data dir',
                                'href' => 'logo.png',
                            ],
                            'hidden' => [
                                'type' => 'hidden',
                                'value' => 'I am not visable',
                            ],
                        ],
                    ],
                    'fieldset_dynamic' => [
                        'type' => 'fieldset',
                        'label' => 'Dynamic elements',
                        'column' => 'is-half',
                        'toggle' => [
                            'field' => 'fieldset0.radioset1',
                            'value' => 'Dynamic elements',
                        ],
                        'children' => [
                            'numberinput1' => [
                                'type' => 'numberinput',
                                'label' => 'Numberinput',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            'fieldset0' => [
                'type' => 'fieldset',
                'column' => 'is-full',
                'children' => [
                    'radioset1' => [
                        'type' => 'radioset',
                        'label' => 'Toggle different fieldsets (hidden fieldsets lose all their values when the form is submitted/saved!)',
                        'column' => 'is-half',
                        'alignment' => 'vertical',
                        'validation' => [
                            'required' => false,
                        ],
                        'choices' => [
                            0 => 'Static elements',
                            1 => 'Dynamic elements',
                        ],
                    ],
                    'fieldset_static' => [
                        'type' => 'fieldset',
                        'label' => 'Static form elements',
                        'column' => 'is-half',
                        'visible' => 'fieldset0.radioset1 == \'Static elements\'',
                        'children' => [
                            'markdown0' => [
                                'type' => 'markdown',
                                'label' => 'markdown',
                                'markdown' => 'Hello _Markdown_!',
                            ],
                            'image0' => [
                                'type' => 'image',
                                'label' => 'Image',
                                'src' => 'logo.png',
                            ],
                            'download0' => [
                                'type' => 'download',
                                'label' => 'Download logo.png from data dir',
                                'href' => 'logo.png',
                            ],
                            'hidden' => [
                                'type' => 'hidden',
                                'value' => 'I am not visable',
                            ],
                        ],
                    ],
                    'fieldset_dynamic' => [
                        'type' => 'fieldset',
                        'label' => 'Dynamic elements',
                        'column' => 'is-half',
                        'visible' => 'fieldset0.radioset1 == \'Dynamic elements\'',
                        'children' => [
                            'numberinput1' => [
                                'type' => 'numberinput',
                                'label' => 'Numberinput',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $actual = \CosmoCode\Formserver\Helper\LegacyHelper::transform($elements);

        $this->assertSame($expected, $actual);
    }
}
