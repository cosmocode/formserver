<?php
declare(strict_types=1);

namespace CosmoCode\Formserver\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

/**
 * Abstract action class which holds basic properties for all descendants
 *
 * @package CosmoCode\Formserver\Actions
 */
abstract class AbstractAction
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $args;

    /**
     * Constructor to inject dependencies
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws HttpNotFoundException
     */
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            return $this->action();
        } catch (\Exception $e) {
            throw new HttpNotFoundException($this->request, $e->getMessage());
        }
    }

    /**
     * Main action method
     *
     * @return Response
     */
    abstract protected function action(): Response;

    /**
     * Resolve argument from mapped route
     *
     * @param  string $name
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name)
    {
        if (! isset($this->args[$name])) {
            throw new HttpBadRequestException(
                $this->request, "Could not resolve argument `{$name}`."
            );
        }

        return $this->args[$name];
    }
}
