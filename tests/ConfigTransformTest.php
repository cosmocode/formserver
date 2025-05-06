<?php

namespace CosmoCode\Formserver\Tests;

use PHPUnit\Framework\TestCase;

class ConfigTransformTest extends TestCase
{
    public function testTransformToggleFlat(): void
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

        $actual = \CosmoCode\Formserver\Helper\ConfigTransformHelper::transform($elements);

        $this->assertSame($expected, $actual);
    }

    public function testTransformMinus(): void
    {
        $elements = [
            'fieldset-1' => [
                'type' => 'fieldset',
                'label' => 'Dynamic elements',
                'column' => 'is-half',
                'children' => [
                    'numberinput-1' => [
                        'type' => 'numberinput',
                        'label' => 'Numberinput',
                    ],
                    'fieldset_1-1' => [
                        'type' => 'fieldset',
                        'toggle' => [
                            'field' => 'fieldset-1.numberinput-1',
                            'value' => '8',
                        ],
                        'children' => [
                            'radioset-1' => [
                                'type' => 'radioset',
                                'label' => 'Choose',
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
                            'dropdown-1' => [
                                'type' => 'dropdown',
                                'label' => 'Conditional',
                                'column' => 'is-half',
                                'alignment' => 'vertical',
                                'validation' => [
                                    'required' => false,
                                ],
                                'conditional_choices' => [
                                    [
                                        'field' => 'fieldset-1.fieldset_1-1.radioset-1',
                                        'value' => 'Static - elements',
                                        'choices' => [
                                            1,
                                            2
                                        ],
                                    ],
                                    [
                                        'field' => 'fieldset-1.fieldset_1-1.radioset-1',
                                        'value' => 'Dynamic - elements',
                                        'choices' => [
                                            "A",
                                            "B"
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ]
        ];

        $expected = [
            'fieldset_1' => [
                'type' => 'fieldset',
                'label' => 'Dynamic elements',
                'column' => 'is-half',
                'children' => [
                    'numberinput_1' => [
                        'type' => 'numberinput',
                        'label' => 'Numberinput',
                    ],
                    'fieldset_1_1' => [
                        'type' => 'fieldset',
                        'visible' => 'fieldset_1.numberinput_1 == \'8\'',
                        'children' => [
                            'radioset_1' => [
                                'type' => 'radioset',
                                'label' => 'Choose',
                                'column' => 'is-half',
                                'alignment' => 'vertical',
                                'validation' => [
                                    'required' => false,
                                ],
                                'choices' => [
                                    0 => 'Static elements',
                                    1 => 'Dynamic elements',
                                ],
                                'transformed_choices' => [
                                    0 => 'Static elements',
                                    1 => 'Dynamic elements',
                                ],
                            ],
                            'dropdown_1' => [
                                'type' => 'dropdown',
                                'label' => 'Conditional',
                                'column' => 'is-half',
                                'alignment' => 'vertical',
                                'validation' => [
                                    'required' => false,
                                ],
                                'conditional_choices' => [
                                    [
                                        'visible' => 'fieldset_1.fieldset_1_1.radioset_1 == \'Static - elements\'',
                                        'choices' => [
                                            1,
                                            2
                                        ],
                                        'transformed_choices' => [
                                            '1',
                                            '2'
                                        ],
                                    ],
                                    [
                                        'visible' => 'fieldset_1.fieldset_1_1.radioset_1 == \'Dynamic - elements\'',
                                        'choices' => [
                                            "A",
                                            "B"
                                        ],
                                        'transformed_choices' => [
                                            "A",
                                            "B"
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $actual = \CosmoCode\Formserver\Helper\ConfigTransformHelper::transform($elements);

        $this->assertSame($expected, $actual);
    }

    public function testTransformToggles(): void
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
        $actual = \CosmoCode\Formserver\Helper\ConfigTransformHelper::transform($elements);

        $this->assertSame($expected, $actual);
    }

    public function testTransformOptions()
    {
        $elements = [
            'rad_conditional' => [
                'type' => 'dropdown',
                'label' => 'Conditional',
                'column' => 'is-one-third',
                'multiselect' => false,
                'validation' => [
                    'required' => true,
                ],
                'conditional_choices' => [
                    0 => [
                        'field' => 'field_waermenetze-pflicht.ansprechpartner1.rad_produkte-einsetzbar',
                        'value' => 'GIRO',
                        'choices' => [
                            0 => 'G1',
                            1 => 'G2',
                        ],
                    ],
                    1 => [
                        'field' => 'field_waermenetze-pflicht.ansprechpartner1.rad_produkte-einsetzbar',
                        'value' => [
                            0 => 'COMFORT',
                            1 => 'PRO',
                        ],
                        'choices' => [
                            0 => 10,
                            1 => 20,
                            2 => 50,
                            3 => 99,
                        ],
                    ],
                ],
            ]
        ];

        $expected = [
            'rad_conditional' => [
                'type' => 'dropdown',
                'label' => 'Conditional',
                'column' => 'is-one-third',
                'multiselect' => false,
                'validation' => [
                    'required' => true,
                ],
                'conditional_choices' => [
                    0 => [
                        'visible' => 'field_waermenetze-pflicht.ansprechpartner1.rad_produkte-einsetzbar == \'GIRO\'',
                        'choices' => [
                            0 => 'G1',
                            1 => 'G2',
                        ],
                    ],
                    1 => [
                        'visible' => 'field_waermenetze-pflicht.ansprechpartner1.rad_produkte-einsetzbar in [\'COMFORT\', \'PRO\']',
                        'choices' => [
                            0 => 10,
                            1 => 20,
                            2 => 50,
                            3 => 99,
                        ],
                    ],
                ],
            ]
        ];

        $actual = \CosmoCode\Formserver\Helper\ConfigTransformHelper::transform($elements);

        $this->assertEquals($expected, $actual);
    }
}
