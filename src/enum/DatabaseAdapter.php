<?php

namespace app\enum;

use app\Application;

/**
 * Enum DatabaseAdapter
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
enum DatabaseAdapter
{
    case MYSQL;
    case ORACLE;
    case POSTGRESQL;

    /**
     * Returns the DatabaseAdapter of the corresponding string value.
     * Returns false if the input is unknown.
     *
     * @param string $adapter
     * @return DatabaseAdapter|false
     */
    public static function resolve(string $adapter): DatabaseAdapter|false {
        switch ($adapter) {
            case "mysql":
                $databaseAdapter = DatabaseAdapter::MYSQL;
                break;
            case "oracle":
                $databaseAdapter = DatabaseAdapter::ORACLE;
                break;
            case "postgresql":
                $databaseAdapter = DatabaseAdapter::POSTGRESQL;
                break;
            default:
                Application::$app->getResponse()->setStatusCode(400);
                Application::$app->getResponse()->setStatusText("Database adapter '$adapter' not found, check config.yaml");
                return false;
        }
        return $databaseAdapter;
    }
}