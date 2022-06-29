<?php

namespace app\enum;

use app\Application;

/**
 * Enum RequestType
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
enum RequestType
{
    case DATABASE;
    case HTTP;

    /**
     * Returns the RequestType of the corresponding string value.
     * Returns false if the input is unknown.
     *
     * @param string $type
     * @return RequestType|false
     */
    public static function resolve(string $type): RequestType|false {
        switch ($type) {
            case "DB":
                $requestType = RequestType::DATABASE;
                break;
            case "HTTP":
                $requestType = RequestType::HTTP;
                break;
            default:
                Application::$app->getResponse()->setStatusCode(400);
                Application::$app->getResponse()->setStatusText("Request type '$type' not found, check config.yaml");
                return false;
        }
        return $requestType;
    }
}