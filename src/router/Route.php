<?php

namespace app\router;

use app\controller\AbstractController;
use app\database\DatabaseData;
use app\enum\RequestType;
use app\enum\RestMethod;
use app\http\HttpData;

/**
 * Represents one route in config.yaml.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class Route
{
    private string $path;
    private AbstractController $controller;
    private RestMethod $method;
    private RequestType $requestType;
    private DatabaseData|HttpData|null $data;
    private ?string $successMessage;
    private array $authRoles;

    /**
     * Route constructor.
     *
     * @param string $path
     * @param AbstractController $controller
     * @param RestMethod $method
     * @param RequestType $requestType
     * @param DatabaseData|HttpData|null $data
     * @param string|null $successMessage
     * @param array $authRoles
     */
    public function __construct(string $path, AbstractController $controller, RestMethod $method,
                                RequestType $requestType, DatabaseData|HttpData|null $data,
                                ?string $successMessage, array $authRoles)
    {
        $this->path = $path;
        $this->controller = $controller;
        $this->method = $method;
        $this->requestType = $requestType;
        $this->data = $data;
        $this->successMessage = $successMessage;
        $this->authRoles = $authRoles;
    }

    /**
     * Path getter.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * AbstractController getter.
     *
     * @return AbstractController
     */
    public function getController(): AbstractController
    {
        return $this->controller;
    }

    /**
     * Rest method getter.
     *
     * @return RestMethod
     */
    public function getMethod(): RestMethod
    {
        return $this->method;
    }

    /**
     * Request type getter.
     *
     * @return RequestType
     */
    public function getRequestType(): RequestType
    {
        return $this->requestType;
    }

    /**
     * DatabaseData getter.
     *
     * @return DatabaseData|HttpData|null
     */
    public function getData(): DatabaseData|HttpData|null
    {
        return $this->data;
    }

    /**
     * SuccessMessage getter.
     *
     * @return string|null
     */
    public function getSuccessMessage(): ?string
    {
        return $this->successMessage;
    }

    /**
     * AuthRoles getter.
     *
     * @return array
     */
    public function getAuthRoles(): array
    {
        return $this->authRoles;
    }
}