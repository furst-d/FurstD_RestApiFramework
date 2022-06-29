<?php

namespace app\http;

use app\enum\ResponseFormat;
use app\enum\RestMethod;

/**
 * Represents data used in HTTP requests.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class HttpData
{
    private RestMethod $method;
    private string $uri;
    private ResponseFormat $responseFormat;
    private ?array $parameters;
    private ?array $content;
    private array $headers;

    /**
     * HttpData constructor.
     *
     * @param RestMethod $method
     * @param string $uri
     * @param ResponseFormat $responseFormat
     * @param array $parameters
     * @param array|null $content
     * @param array $headers
     */
    public function __construct(RestMethod $method, string $uri, ResponseFormat $responseFormat, array $parameters, ?array $content, array $headers)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->responseFormat = $responseFormat;
        $this->parameters = $parameters;
        $this->content = $content;
        $this->headers = $headers;
    }

    /**
     * RestMethod getter.
     *
     * @return RestMethod
     */
    public function getMethod(): RestMethod
    {
        return $this->method;
    }

    /**
     * URI getter.
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * URI setter.
     *
     * @param string $uri
     */
    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }

    /**
     * ResponseFormat getter.
     *
     * @return ResponseFormat
     */
    public function getResponseFormat(): ResponseFormat
    {
        return $this->responseFormat;
    }

    /**
     * Parameters getter.
     *
     * @return array|null
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    /**
     * @return array|null
     */
    public function getContent(): ?array
    {
        return $this->content;
    }

    /**
     * Headers getter.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}