<?php

namespace app\http;

use app\Application;
use app\Request;
use app\Response;
use app\router\Route;

/**
 * Class used for composing HTTP query strings.
 */
class HttpBuilder
{
    /**
     * Checks and update URI according to config parameters.
     *
     * @param Request $request
     * @param Response $response
     * @param Route $route
     * @return bool
     */
    public function buildQueryString(Request $request, Response $response, Route $route): bool {
        $data = $route->getData();
        $uri = $data->getUri();
        foreach ($data->getParameters() as $parameter) {
            $name = $parameter->getName();
            if($param = $this->getParameter($parameter, $request)) {
                if(!$this->checkType($parameter, $response, $param)) {
                    return false;
                }
                $uri = str_replace("{" . $name . "}", rawurlencode($param), $uri);
            } else {
                return false;
            }
        }
        $data->setUri($uri);
        return true;
    }

    /**
     * Returns parameter depending on inserted configuration data.
     * Returns false if parameter was not found.
     *
     * @param HttpParameter $parameter
     * @param Request $request
     * @return string|null
     */
    private function getParameter(HttpParameter $parameter, Request $request): ?string {
        $param = $request->getParameter($parameter, $request);

        if($parameter->getRequired() && $param == null) {
            Application::$app->getResponse()->setStatusCode(400);
            Application::$app->getResponse()->setStatusText("Required parameter '" .
                $parameter->getName() . "' not found");
            return false;
        }
        return $param;
    }

    /**
     * Checks if the column value can be converted to defined type.
     *
     * @param HttpParameter $parameter
     * @param Response $response
     * @param string $value
     * @return bool
     */
    private function checkType(HttpParameter $parameter, Response $response, string $value): bool {
        if($parameter->getType() == null) {
            return true;
        }

        $temp = $value;
        settype($value, $parameter->getType());
        if($temp != $value) {
            $response->setStatusCode(400);
            $response->setStatusText("Parameter '"
                . $parameter->getName() . "' must be " . $parameter->getType());
            return false;
        }

        return true;
    }
}