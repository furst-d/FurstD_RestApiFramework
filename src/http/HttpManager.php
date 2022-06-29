<?php

namespace app\http;

use app\Content;
use app\enum\ResponseFormat;
use app\enum\RestMethod;
use app\Request;
use app\Response;
use CurlHandle;
use ValueError;

/**
 * Class working with cURL.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class HttpManager
{
    private HttpData $data;
    private Request $request;
    private Response $response;
    private CurlHandle $ch;

    /**
     * HttpManager constructor.
     *
     * @param HttpData $data
     * @param Request $request
     * @param Response $response
     */
    public function __construct(HttpData $data, Request $request, Response $response)
    {
        $this->data = $data;
        $this->request = $request;
        $this->response = $response;
        $this->ch = curl_init($data->getUri());
    }

    /**
     * Sets cURL options depending on Rest method, sends request and sets response.
     *
     * @return void
     */
    public function send(): void
    {
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->data->getHeaders());

        $content = $this->checkContent();
        if($content != -1) {
            switch ($this->data->getMethod()) {
                case RestMethod::POST:
                    curl_setopt($this->ch, CURLOPT_POST, true);
                    curl_setopt($this->ch, CURLOPT_POSTFIELDS, $content);
                    break;
                case RestMethod::PUT:
                    curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT");
                    curl_setopt($this->ch, CURLOPT_POSTFIELDS, $content);
                    break;
                case RestMethod::DELETE:
                    curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                    break;
                default:
                    break;
            }

            $result = curl_exec($this->ch);
            $this->setResponse($result);
            curl_close($this->ch);
        }
    }

    /**
     * Checks and sets corresponding response code, message and content depending on result and response format.
     *
     * @param string|false $result
     * @return void
     */
    private function setResponse(string|false $result): void
    {
        if (curl_getinfo($this->ch, CURLINFO_HTTP_CODE) != 200) {
            $this->response->setStatusCode(curl_getinfo($this->ch, CURLINFO_HTTP_CODE));
            $error = curl_error($this->ch);
            $this->response->setStatusText(empty($error) ? "cURL returned error" : $error);
            return;
        }

        $responseFormat = $this->data->getResponseFormat();
        if(!$this->checkResponseType($responseFormat, $result)) {
            $this->response->setStatusCode(400);
            $this->response->setStatusText("Returned data does not match response format");
            return;
        }

        switch ($responseFormat) {
            case ResponseFormat::JSON:
                $this->response->setContent(json_decode($result, true));
                break;
            case ResponseFormat::XML:
                $xml = simplexml_load_string($result, "SimpleXMLElement", LIBXML_NOCDATA);
                $json = json_encode($xml);
                $this->response->setContent(json_decode($json, true));
                break;
            case ResponseFormat::TEXT:
                $this->response->setContent(array($result));
                break;
            default:
                break;
        }
    }

    /**
     * Checks if result is of the required type.
     *
     * @param ResponseFormat $format
     * @param string $result
     * @return bool
     */
    private function checkResponseType(ResponseFormat $format, string $result): bool
    {
        switch ($format) {
            case ResponseFormat::JSON:
                json_decode($result);
                return json_last_error() === JSON_ERROR_NONE;
            case ResponseFormat::XML:
                libxml_use_internal_errors(true);
                return (bool)simplexml_load_string($result);
            default:
                return true;
        }
    }

    /**
     * Checks and returns content body.
     * Returns -1 if content value is incorrect.
     *
     * @return array|int|null
     */
    private function checkContent(): array|int|null {
        $content = json_decode($this->request->getContent(), true);
        foreach ($this->data->getContent() as $column) {
            $contentValue = $this->getContentValue($content, $column);

            if($contentValue == -1 || !$this->checkRequiredContentExists($column, $contentValue)) {
                return -1;
            }

            if($contentValue != null) {
                if(!$this->checkType($column, $contentValue)) {
                    return -1;
                }
                $content[$column->getName()] = $contentValue;
            }
        }
        return $content;
    }

    /**
     * Checks if the column value can be converted to defined type.
     *
     * @param Content $content
     * @param string $value
     * @return bool
     */
    private function checkType(Content $content, string $value): bool {
        if($content->getType() == null) {
            return true;
        }

        $temp = $value;
        settype($value, $content->getType());
        if($temp != $value) {
            $this->response->setStatusCode(400);
            $this->response->setStatusText("Parameter '"
                . $content->getName() . "' must be " . $content->getType());
            return false;
        }

        return true;
    }

    /**
     * Returns content value.
     * If there is a hash set, returns hashed values.
     * Returns -1 if hash algorithm is not supported.
     *
     * @param array $content
     * @param Content $column
     * @return string|int|null
     */
    private function getContentValue(array $content, Content $column): string|int|null
    {
        $contentValue = $content[$column->getName()] ?? null;
        $hash = $column->getHash();
        if($hash != null) {
            try {
                $contentValue = hash($column->getHash(), $contentValue);
            } catch (ValueError) {
                $this->response->setStatusCode(400);
                $this->response->setStatusText("Hash algorithm '" . $column->getHash() . "' is not supported");
                return -1;
            }
        }
        return $contentValue;
    }

    /**
     * Checks if the required content value exists.
     *
     * @param Content $column
     * @param string|null $contentValue
     * @return bool
     */
    private function checkRequiredContentExists(Content $column, ?string $contentValue): bool {
        if($contentValue == null && $column->isRequired()) {
            $this->response->setStatusCode(400);
            $this->response->setStatusText("Required value '" . $column->getName() . "' not found");
            return false;
        }
        return true;
    }
}