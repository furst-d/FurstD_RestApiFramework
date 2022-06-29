<?php

namespace app\enum;

use app\Application;

/**
 * Enum ResponseFormat.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
enum ResponseFormat
{
    case NONE;
    case JSON;
    case XML;
    case TEXT;

    /**
     * Returns the ResponseFormat of the corresponding string value.
     * Returns false if the input is unknown.
     *
     * @param string|null $format
     * @return ResponseFormat|false
     */
    public static function resolve(?string $format): ResponseFormat|false {
        if($format == null) {
            Application::$app->getResponse()->setStatusCode(400);
            Application::$app->getResponse()->setStatusText("Response format not found, check config.yaml");
            return false;
        }
        switch ($format) {
            case "NONE":
                $responseType = ResponseFormat::NONE;
                break;
            case "JSON":
                $responseType = ResponseFormat::JSON;
                break;
            case "XML":
                $responseType = ResponseFormat::XML;
                break;
            case "TEXT":
                $responseType = ResponseFormat::TEXT;
                break;
            default:
                Application::$app->getResponse()->setStatusCode(400);
                Application::$app->getResponse()->setStatusText("Response format '$format' not found, check config.yaml");
                return false;
        }
        return $responseType;
    }
}