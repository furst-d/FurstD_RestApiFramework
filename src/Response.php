<?php

namespace app;

/**
 * Represents a HTTP response.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class Response
{
    private string|array $content;
    private int $statusCode;
    private string $statusText;

    /**
     * Response constructor.
     */
    public function __construct()
    {
        $this->statusCode = 200;
        $this->statusText = "OK";
    }

    /**
     * Content setter.
     *
     * @param array|string $content
     */
    public function setContent(array|string $content): void
    {
        $this->content = $content;
    }

    /**
     * StatusText setter.
     *
     * @param string $statusText
     */
    public function setStatusText(string $statusText): void
    {
        $this->statusText = $statusText;
    }

    /**
     * StatusCode getter.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * StatusCode setter
     *
     * @param int $code
     * @return void
     */
    public function setStatusCode(int $code): void
    {
        $this->statusCode = $code;
        http_response_code($code);
    }

    /**
     * Sends a JSON message to the user.
     *
     * @return void
     */
    public function send(): void
    {
        $message = array(
            "status_code" => $this->statusCode,
            "status_message" => $this->statusText);
        if(isset($this->content)) {
            $message['data'] = $this->content;
        }
        echo json_encode($message);
    }
}