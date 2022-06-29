<?php

namespace app\database;

use app\Application;
use app\enum\RestMethod;
use app\Response;
use mysqli;
use mysqli_sql_exception;

/**
 * Class working with MySQL database.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class SQLManager
{
    private ?mysqli $connection;
    private string $query;
    private Response $response;
    private RestMethod $method;

    /**
     * SQLManager constructor.
     *
     * @param string $query
     * @param Response $response
     * @param RestMethod $method
     */
    public function __construct(string $query, Response $response, RestMethod $method)
    {
        $this->connection = Application::$app->getDatabase()->getConnection();
        $this->query = $query;
        $this->response = $response;
        $this->method = $method;
    }

    /**
     * Sends SQL query and sets response.
     *
     * @return void
     */
    public function send(): void
    {
        try {
            if($this->connection == null) {
                $this->response->setStatusCode(404);
                $this->response->setStatusText("Database is disabled");
                return;
            }
            if($this->method == RestMethod::GET) {
                $result = $this->connection->query($this->query)->fetch_all(MYSQLI_ASSOC);
                if (sizeof($result) > 0) {
                    $this->response->setContent($result);
                    return;
                }
                $this->response->setStatusCode(404);
                $this->response->setStatusText("No data found");
            } else {
                $stmt = $this->connection->prepare($this->query);
                $stmt->execute();
            }

        } catch (mysqli_sql_exception $e) {
            $this->response->setStatusCode(400);
            $this->response->setStatusText($e->getMessage());
        }
    }
}