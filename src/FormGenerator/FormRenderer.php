<?php

declare(strict_types=1);

namespace CosmoCode\Formserver\FormGenerator;

use CosmoCode\Formserver\Exceptions\TwigException;
use CosmoCode\Formserver\Service\LangManager;
use Twig\TemplateWrapper;

/**
 * Renders twig blocks
 */
class FormRenderer
{
    public const TEMPLATE_DIR = __DIR__ . '/../../view/';

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

        // Global variables available in all templates and macros
        $this->twig->addGlobal('configJSON', $this->form->getJSON());

        $this->twig->addGlobal('formId', $this->form->getId());
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



        return $this->renderTemplate(
            '_form',
            [
                'title' => $this->form->getMeta('title'),
                'css' => $this->form->getMeta('css'),
                'formId' => $this->form->getId(),
                'logo' => $this->form->getMeta('logo'),
                'save_button_visible' => $this->form->getMeta('saveButton') ?? true,
                'send_button_visible' => ($this->form->getMeta('email') || $this->form->getMeta('export')) ?? false,
            ]
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
}
