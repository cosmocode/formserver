<?php
declare(strict_types=1);

namespace CosmoCode\Formserver\Actions;

use CosmoCode\Formserver\Exceptions\MailException;
use CosmoCode\Formserver\FormGenerator\Form;
use CosmoCode\Formserver\FormGenerator\FormRenderer;
use CosmoCode\Formserver\FormGenerator\FormValidator;
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
     * Constructor to inject dependencies
     *
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
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
        } catch (HttpBadRequestException $e) {
            $this->response->getBody()->write(LangManager::getString('error_notfound'));
        } catch (MailException $e) {
            $this->response->getBody()->write(LangManager::getString('send_failed'));
        }

        return $this->response;
    }
}
