<?php
declare(strict_types=1);

namespace CosmoCode\Formserver\Actions;

use CosmoCode\Formserver\FormGenerator\Form;
use CosmoCode\Formserver\FormGenerator\FormRenderer;
use CosmoCode\Formserver\FormGenerator\FormValidator;
use CosmoCode\Formserver\Service\Mailer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
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
     * Constructor to inject dependencies
     *
     * @param LoggerInterface $logger
     * @param Mailer $mailer
     */
    public function __construct(LoggerInterface $logger, Mailer $mailer)
    {
        parent::__construct($logger);
        $this->mailer = $mailer;
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
            $formRenderer = new FormRenderer($form);
            $formValidator = new FormValidator($form);


            if ($this->request->getMethod() === 'POST') {
                $form->submit(
                    $this->request->getParsedBody(),
                    $this->request->getUploadedFiles()
                );
                $formValidator->validate();
                $form->persist();

                if ($form->isValid() && $form->getMode() === Form::MODE_SEND) {
                    $this->mailer->sendForm($form);
                }
            } elseif ($this->request->getMethod() === 'GET') {
                $form->restore();
            }

            $formHtml = $formRenderer->render();
            $this->response->getBody()->write($formHtml);

            $this->logger->info("Form $id was viewed");
        } catch (HttpBadRequestException $e) {
            $this->response->getBody()->write('Forbidden: no form ID!');
            $this->logger->error("Missing form ID");
        }

        return $this->response;
    }
}
