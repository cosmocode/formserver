<?php
declare(strict_types=1);

namespace CosmoCode\Formserver\Actions;

use CosmoCode\Formserver\FormGenerator\Form;
use CosmoCode\Formserver\FormGenerator\FormRenderer;
use CosmoCode\Formserver\Helper\YamlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class FormAction extends AbstractAction
{
    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        try {
            $id = $this->resolveArg('id');
            $config = YamlHelper::parseYaml($id);

            $form = new Form($config['form']);
            if ($this->request->getMethod() === 'POST') {
                $form->submitData($this->request->getParsedBody());
            }
            $formRenderer = new FormRenderer();

            $formHtml = $formRenderer->renderForm($form->getFormElements(), $config['meta']['title'] ?? 'Form Title');
            $this->response->getBody()->write($formHtml);

            $this->logger->info("Form $id was viewed");
        } catch (HttpBadRequestException $e) {
            $this->response->getBody()->write('Forbidden: no form ID!');
            $this->logger->error("Missing form ID");
        }

        return $this->response;
    }
}
