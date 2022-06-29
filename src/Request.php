<?php

namespace app;

use app\enum\RestMethod;

/**
 * Represents a HTTP request.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class Request
{
    private array $routeParams;
    private array $queryParams;
    private RestMethod $restMethod;
    private string|bool $content;

    /**
     * Request constructor.
     * Stores the method and content.
     * If the REST method is unknown or the request body is empty for Patch, Post, or Put methods,
     * it sets the appropriate status code and status message to the response.
     */
    public function __construct()
    {
        $method = $this->resolveMethod();
        if(!$method) {
            Application::$app->getResponse()->setStatusCode(400);
            Application::$app->getResponse()->setStatusText("Unsupported request method");
        }
        $this->restMethod = $method;
        $this->content = false;
        if($this->restMethod == RestMethod::PATCH
            || $this->restMethod == RestMethod::POST
            || $this->restMethod == RestMethod::PUT) {
            $this->content = file_get_contents('php://input');
            if(!$this->content) {
                Application::$app->getResponse()->setStatusCode(400);
                Application::$app->getResponse()->setStatusText("No body found");
            }
        }

        $this->queryParams = [];
        parse_str($_SERVER['QUERY_STRING'] ?? null, $this->queryParams);
    }


    /**
     * Route parameters getter.
     *
     * @return array
     */
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    /**
     * Query parameters getter.
     *
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * Returns parameter according to config.
     *
     * @param $parameterConfig
     * @param Request $request
     * @return string|null
     */
    public function getParameter($parameterConfig, Request $request): ?string {
        if($parameterConfig->isEditable()) {
            $param = $parameterConfig->getLocation() == "query"
                ? $request->getQueryParams()[$parameterConfig->getName()] ?? null
                : $request->getRouteParams()[$parameterConfig->getName()] ?? null;
            $param = $param == null && $parameterConfig->getDefault() != null
                ? $parameterConfig->getDefault() : $param;
        } else {
            $param = $parameterConfig->getDefault();
        }
        return $param;
    }

    /**
     * Route parameters setter.
     *
     * @param array $routeParams
     */
    public function setRouteParams(array $routeParams): void
    {
        $this->routeParams = $routeParams;
    }

    /**
     * Rest method getter.
     *
     * @return RestMethod
     */
    public function getRestMethod(): RestMethod
    {
        return $this->restMethod;
    }

    /**
     * Content getter.
     *
     * @return bool|string
     */
    public function getContent(): bool|string
    {
        return $this->content;
    }

    /**
     * URL getter.
     * Returns a URL without parameters.
     *
     * @return string
     */
    public function getUrl(): string
    {
        $path = $_SERVER['REQUEST_URI'];
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        return $path;
    }

    /**
     * It checks the request method and returns the corresponding RestMethod, or false if the method is not supported.
     *
     * @return RestMethod|false
     */
    private function resolveMethod(): RestMethod|false {
        return match ($_SERVER['REQUEST_METHOD']) {
            "GET" => RestMethod::GET,
            "POST" => RestMethod::POST,
            "PUT" => RestMethod::PUT,
            "DELETE" => RestMethod::DELETE,
            "PATCH" => RestMethod::PATCH,
            default => false,
        };
    }
}