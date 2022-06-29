<?php

namespace app\enum;

use app\Application;

/**
 * Enum RestMethod.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
enum RestMethod {
    case GET;
    case POST;
    case PUT;
    case DELETE;
    case PATCH;

    /**
     * Returns the RestMethod of the corresponding string value.
     * Returns false if the input is unknown.
     *
     * @param string $method
     * @return RestMethod|false
     */
    public static function resolve(string $method): RestMethod|false {
        switch ($method) {
            case "GET":
                $restMethod = RestMethod::GET;
                break;
            case "POST":
                $restMethod = RestMethod::POST;
                break;
            case "PUT":
                $restMethod = RestMethod::PUT;
                break;
            case "DELETE":
                $restMethod = RestMethod::DELETE;
                break;
            case "PATCH":
                $restMethod = RestMethod::PATCH;
                break;
            default:
                Application::$app->getResponse()->setStatusCode(400);
                Application::$app->getResponse()->setStatusText("Rest method '$method' not found, check config.yaml");
                return false;
        }
        return $restMethod;
    }
}


