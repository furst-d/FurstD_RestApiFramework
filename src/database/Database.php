<?php

namespace app\database;

use app\Application;
use app\enum\DatabaseAdapter;
use app\utils\YamlConfig;
use mysqli;
use mysqli_sql_exception;

/**
 * Class Database.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class Database
{
    private DatabaseAdapter $adapter;
    private mysqli $connection;

    /**
     * Database constructor.
     * Retrieves connection data from config and creates a connection.
     * If a connection error occurs, it sets the appropriate status code and status message to the response.
     */
    public function __construct()
    {
        $config = YamlConfig::loadFile("database.yaml");

        $adapter = DatabaseAdapter::resolve($config['adapter']) ?? DatabaseAdapter::MYSQL;
        $db_name = $config['db_name'];
        $host = $config['host'];
        $username = $config['username'];
        $password = $config['password'];
        $charset = $config['charset'];

        if($adapter) {
            $this->adapter = $adapter;
            try {
                $connection = new mysqli ($host, $username, $password, $db_name);
                $connection->set_charset($charset);
                $this->connection = $connection;
                $this->createAuthTables($config['auth_prefix'] ?? null);
            } catch(mysqli_sql_exception $e) {
                Application::$app->getResponse()->setStatusCode(400);
                Application::$app->getResponse()->setStatusText($e->getMessage());
                return;
            }
        }
    }

    /**
     * Creates authorization tables if not exists.
     *
     * @param string|null $prefix
     * @return void
     */
    private function createAuthTables(?string $prefix): void
    {
        $stmt = $this->connection->prepare("CREATE TABLE IF NOT EXISTS " . $prefix . "roles (role_id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT, name varchar(255) NOT NULL)");
        $stmt->execute();
        $stmt = $this->connection->prepare("CREATE TABLE IF NOT EXISTS " . $prefix . "users (user_id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT, username varchar(255) NOT NULL, password varchar(255) NOT NULL, role_id int(11) NOT NULL, FOREIGN KEY (role_id) REFERENCES " . $prefix . "roles (role_id) ON UPDATE CASCADE ON DELETE RESTRICT)");
        $stmt->execute();
    }

    /**
     * Adapter getter.
     *
     * @return DatabaseAdapter
     */
    public function getAdapter(): DatabaseAdapter
    {
        return $this->adapter;
    }

    /**
     * Connection getter.
     * @return mysqli
     */
    public function getConnection(): mysqli
    {
        return $this->connection;
    }
}