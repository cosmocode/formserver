<?php
declare(strict_types=1);

namespace CosmoCode\Formserver\Actions;

use CosmoCode\Formserver\Exceptions\FormException;
use CosmoCode\Formserver\Exceptions\LanguageException;
use CosmoCode\Formserver\Exceptions\MailException;
use CosmoCode\Formserver\FormGenerator\Form;
use CosmoCode\Formserver\FormGenerator\FormRenderer;
use CosmoCode\Formserver\FormGenerator\FormValidator;
use CosmoCode\Formserver\Service\FileExporter;
use CosmoCode\Formserver\Service\LangManager;
use CosmoCode\Formserver\Service\Mailer;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

/**
 * Handles form requests
 *
 * @package CosmoCode\Formserver\Actions
 */
class FormAction extends AbstractAction
{
    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var FileExporter
     */
    protected $fileExporter;

    /**
     * Constructor to inject dependencies
     *
     * @param Mailer $mailer
     * @param FileExporter $fileExporter
     */
    public function __construct(Mailer $mailer, FileExporter $fileExporter)
    {
        $this->mailer = $mailer;
        $this->fileExporter = $fileExporter;
    }

    /**
     * Action to provide a form
     *
     * @return Response
     */
    protected function action(): Response
    {
        try {
            $id = $this->resolveArg('id');
            $form = new Form($id);

            LangManager::init($form->getMeta('language'));
            $formRenderer = new FormRenderer($form);
            $formValidator = new FormValidator($form);


            if ($this->request->getMethod() === 'POST') {
                $form->submit(
                    $this->request->getParsedBody(),
                    $this->request->getUploadedFiles()
                );
                $formValidator->validate();

                if ($form->getMeta('saveButton') !== false) {
                    $form->persist();
                }

                if ($form->isValid() && $form->getMode() === Form::MODE_SEND) {
                    $this->mailer->sendForm($form);
                    $this->handleFileExport($form);
                    // finally clean up if it is a non-persistent form
                    if ($form->getMeta('saveButton') === false) {
                        $formElements = $form->getFormElements();
                        $form->reset($formElements);
                    }
                }
            } elseif ($this->request->getMethod() === 'GET') {
                $form->restore();
            }

            $formHtml = $formRenderer->render();
            $this->response->getBody()->write($formHtml);
        } catch (HttpBadRequestException $e) {
            $this->response->getBody()->write(LangManager::getString('error_notfound'));
        } catch (MailException $e) {
            $this->response->getBody()->write(LangManager::getString('send_failed'));
        }

        return $this->response;
    }

    /**
     * Helper function to copy file to another location when form gets sent
     *
     * @param Form $form
     * @return void
     * @throws FormException
     */
    protected function handleFileExport(Form $form)
    {
        $file = $form->getMeta('export') ?? '';
        if ($file !== '') {
            $formId = $form->getId();
            $filePath = $form->getFormDirectory() . $file;

            $this->fileExporter->moveFile($filePath, $formId);
        }
    }
}
