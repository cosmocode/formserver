<?php
declare(strict_types=1);

namespace CosmoCode\Formserver\Actions;

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
            $this->response->getBody()->write("Hello Form ID $id !");
            $this->logger->info("Form $id was viewed");
        } catch (HttpBadRequestException $e) {
            $this->response->getBody()->write('Forbidden: no form ID!');
            $this->logger->error("Missing form ID");
        }

        return $this->response;
    }
}
