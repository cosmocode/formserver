<?php

namespace CosmoCode\Formserver\FormGenerator;

use CosmoCode\Formserver\Exceptions\TwigException;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractFormElement;
use CosmoCode\Formserver\Service\LangManager;
use Twig\TemplateWrapper;

/**
 * Renders twig blocks
 */
class FormRenderer
{
    const TEMPLATE_DIR = __DIR__ . '/../../view/';

    /**
     * @var TemplateWrapper
     */
    protected $twig;

    /**
     * @var Form
     */
    protected $form;

    /**
     * Pass the form which later should be rendered
     *
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;

        $templates = array_diff(
            scandir(self::TEMPLATE_DIR),
            ['..', '.']
        );
        $loadedTemplates = [];

        foreach ($templates as $formElementTemplate) {
            $templateName = str_replace('.twig', '', $formElementTemplate);
            $loadedTemplates[$templateName]
                = file_get_contents(self::TEMPLATE_DIR . $formElementTemplate);
        }

        $arrayLoader = new \Twig\Loader\ArrayLoader($loadedTemplates);

        $this->twig = new \Twig\Environment($arrayLoader, ['debug' => true]);
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());
    }

    /**
     * Renders the complete Form
     *
     * @return string
     * @throws TwigException
     */
    public function render()
    {
        $renderedFormElements = [];

        // Global variables available in all templates and macros
        $this->twig->addGlobal('form_id', $this->form->getId());
        $this->twig->addGlobal('form_is_valid', $this->form->isValid());
        $this->twig->addGlobal('button_save_label', LangManager::getString('button_save'));
        $this->twig->addGlobal('button_send_label', LangManager::getString('button_send'));
        $this->twig->addGlobal('button_clone_label', LangManager::getString('button_clone'));
        $this->twig->addGlobal('button_upload_label', LangManager::getString('button_upload'));
        $this->twig->addGlobal('button_upload_replace', LangManager::getString('button_upload_replace'));
        $this->twig->addGlobal('uploaded_file_label', LangManager::getString('uploaded_file'));
        $this->twig->addGlobal('uploaded_original', LangManager::getString('uploaded_original'));
        $this->twig->addGlobal('upload_info', LangManager::getString('upload_info'));
        $this->twig->addGlobal('upload_error', LangManager::getString('upload_error'));
        $this->twig->addGlobal('tooltip_style', $this->form->getMeta('tooltip_style') ?? '');

        foreach ($this->form->getFormElements() as $formElement) {
            if ($formElement instanceof FieldsetFormElement) {
                $renderedFormElements[] = $this->renderFieldsetFormElement(
                    $formElement
                );
            } else {
                $renderedFormElements[] = $this->renderTemplate(
                    $formElement->getType(),
                    $formElement->getViewVariables()
                );
            }
        }

        return $this->renderTemplate(
            '_form',
            [
                'rendered_form_elements' => $renderedFormElements,
                'title' => $this->form->getMeta('title'),
                'notification' => $this->generateNotification(),
                'css' => $this->form->getMeta('css'),
                'form_id' => $this->form->getId(),
                'logo' => $this->form->getMeta('logo'),
                'save_button_visible' => $this->form->getMeta('saveButton') ?? true,
                'send_button_visible' => ($this->form->getMeta('email') || $this->form->getMeta('export')) ?? false,
            ]
        );
    }

    /**
     * Renders the view of a FormElement
     *
     * @param FieldsetFormElement $fieldsetFormElement
     * @return string
     * @throws TwigException
     */
    protected function renderFieldsetFormElement(
        FieldsetFormElement $fieldsetFormElement
    ) {
        foreach ($fieldsetFormElement->getChildren() as $childFormElement) {
            if ($childFormElement instanceof FieldsetFormElement) {
                $renderedChildView = $this->renderFieldsetFormElement(
                    $childFormElement
                );
            } else {
                $renderedChildView = $this->renderTemplate(
                    $childFormElement->getType(),
                    $childFormElement->getViewVariables()
                );
            }
            $fieldsetFormElement->addRenderedChildView($renderedChildView);
        }

        return $this->renderTemplate(
            $fieldsetFormElement->getType(),
            $fieldsetFormElement->getViewVariables()
        );
    }

    /**
     * Helper function to render a twig block
     *
     * @param string $template
     * @param array $variables
     * @return string
     * @throws TwigException
     */
    protected function renderTemplate(string $template, array $variables)
    {
        try {
            return $this->twig->render($template, $variables);
        } catch (\Throwable $e) {
            throw new TwigException(
                "Could not render form element '$template': " . $e->getMessage()
            );
        }
    }

    /**
     * Generate global form notification, to indicate to the user what happened
     *
     * @return string|null
     */
    protected function generateNotification()
    {
        $formMode = $this->form->getMode();
        $formValid = $this->form->isValid();

        switch ($formMode) {
            case Form::MODE_SAVE:
                if ($formValid) {
                    return LangManager::getString('form_valid');
                }

                return LangManager::getString('form_invalid');
            case Form::MODE_SEND:
                if ($formValid) {
                    return LangManager::getString('send_success');
                }

                return LangManager::getString('send_prevented');
            case Form::MODE_SHOW:
                return null;
        }
    }
}
