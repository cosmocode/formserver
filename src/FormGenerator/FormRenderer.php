<?php

namespace CosmoCode\Formserver\FormGenerator;


use CosmoCode\Formserver\Exceptions\TwigException;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractFormElement;
use Twig\TemplateWrapper;

/**
 * Renders twig blocks
 */
class FormRenderer
{
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

        $twigLoader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../view/');
        $twigEnvironment = new \Twig\Environment($twigLoader);
        try {
            $this->twig = $twigEnvironment->load('layout.twig');
        } catch (\Twig\Error\Error $e) {
            throw new TwigException(
                "Could not load twig layout file:" . $e->getMessage()
            );
        }
    }

    /**
     * Renders the complete Form
     *
     * @return string
     * @throws TwigException
     */
    public function render()
    {
        $formHtml = '';
        $title = $this->form->getMeta('title');
        foreach ($this->form->getFormElements() as $formElement) {
            if ($formElement instanceof FieldsetFormElement) {
                $formHtml .= $this->renderFieldsetFormElement($formElement);
            } else {
                $formHtml .= $this->renderFormElement($formElement);
            }
        }

        return $this->renderBlock(
            'form',
            [
                'formHtml' => $formHtml,
                'title' => $title,
                'is_valid' => $this->form->isValid(),
                'notification' => $this->generateNotification(),
                'css' => $this->form->getMeta('css'),
                'form_id' => $this->form->getId(),
                'logo' => $this->form->getMeta('logo')
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
            $fieldsetFormElement->addRenderedChildView(
                $this->renderFormElement($childFormElement)
            );
        }

        return $this->renderFormElement($fieldsetFormElement);
    }

    /**
     * Helper function to render a FormElement
     *
     * @param AbstractFormElement $formElement
     * @return string
     */
    protected function renderFormElement(AbstractFormElement $formElement)
    {
        return $this->renderBlock(
            $formElement->getType(),
            $formElement->getViewVariables()
        );
    }

    /**
     * Helper function to render a twig block
     *
     * @param string $block
     * @param array $variables
     * @return string
     * @throws TwigException
     */
    protected function renderBlock(string $block, array $variables)
    {
        if (! $this->twig->hasBlock($block)) {
            throw new TwigException(
                "Template block for form element type '$block' not found."
            );
        }

        // Global variables
        $variables['form_id'] = $this->form->getId();

        try {
            return $this->twig->renderBlock($block, $variables);
        } catch (\Throwable $e) {
            throw new TwigException(
                "Could not render block '$block': " . $e->getMessage()
            );
        }
    }

    /**
     * Generate global form notification, to indicate to the user what happened
     * TODO: No hardcoded texts
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
                    return 'Formular wurde erfolgreich gespeichert.';
                }

                return 'Das Formular wurde erfolgreich gespeichert. Es enthält jedoch noch Fehler.';
            case Form::MODE_SEND:
                if ($formValid) {
                    return 'Das Formular wurde erfolgreich abgeschickt.';
                }

                return 'Das Formular konnte nicht abgeschickt werden. Bitte korrigieren Sie die fehlerhaften Felder';
            case Form::MODE_SHOW:
                return null;
        }
    }
}
