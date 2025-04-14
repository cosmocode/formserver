<?php

declare(strict_types=1);

namespace CosmoCode\Formserver\Actions;

use CosmoCode\Formserver\Exceptions\FormException;
use CosmoCode\Formserver\Exceptions\MailException;
use CosmoCode\Formserver\FormGenerator\Form;
use CosmoCode\Formserver\FormGenerator\FormRenderer;
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
    protected Mailer $mailer;

    /**
     * @var FileExporter
     */
    protected FileExporter $fileExporter;

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
        $code = 200;
        try {
            $id = $this->resolveArg('id');
            $form = new Form($id);

            $formRenderer = new FormRenderer($form);

            if ($this->request->getMethod() === 'POST') {
                $body = $this->request->getParsedBody();

                // TODO process files
                if ($form->isPersistent()) {
                    $form->persist($body['data']);
                }

                if ($body['mode'] === Form::MODE_SEND) {
//                    $this->mailer->sendForm($form, $body['data']);
                    $this->handleFileExport($form);
                }

                $this->response->getBody()->write(json_encode('ok'));
                return $this->response
                    ->withStatus($code)
                    ->withHeader('content-type', 'application/json');
            }

            $formHtml = $formRenderer->render();
            $this->response->getBody()->write($formHtml);
        } catch (HttpBadRequestException $e) {
            $this->response->getBody()->write(LangManager::getString('error_notfound'));
            $code = $e->getCode();
        } catch (MailException $e) {
            $this->response->getBody()->write(LangManager::getString('send_failed'));
            $code = 500;
        }

        return $this->response->withStatus($code);
    }

    /**
     * Helper function to copy file to another location when form gets sent
     *
     * @param Form $form
     * @return void
     * @throws FormException
     */
    protected function handleFileExport(Form $form): void
    {
        $file = $form->getMeta('export') ?? '';
        if ($file !== '') {
            $formId = $form->getId();
            $filePath = $form->getFormDirectory() . $file;

            $this->fileExporter->moveFile($filePath, $formId);
        }
    }
}
