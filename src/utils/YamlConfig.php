<?php

namespace app\utils;

use app\Application;
use Symfony\Component\Yaml\Yaml;

/**
 * Class for working with .yaml files.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class YamlConfig
{
    /**
     * Loads a .yaml file into the array.
     * Returns false if the file does not exist.
     *
     * @param $fileName
     * @return array|false
     */
    public static function loadFile($fileName): array|false
    {
        $path = dirname(__FILE__, 6) . '/config/' . $fileName;
        if(!file_exists($path)) {
            Application::$app->getResponse()->setStatusCode(400);
            Application::$app->getResponse()->setStatusText("File $fileName not found");
            return false;
        }
        return Yaml::parse(file_get_contents(dirname(__FILE__, 6) . '/config/' . $fileName));
    }
}