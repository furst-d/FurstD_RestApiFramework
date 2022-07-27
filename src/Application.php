<?php

namespace app;

use app\router\Router;
use app\database\Database;

/**
 * Represents an Application.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class Application
{
    public static Application $app;
    private Request $request;
    private Response $response;
    private Router $router;
    private Database $database;

    /**
     * Application constructor.
     */
    public function __construct()
    {
        self::$app = $this;
		$this->setHeaders();
        $this->response = new Response();
        $this->request = new Request();
        $this->database = new Database();
        $this->router = new Router($this->request, $this->response);
    }

    /**
     * Request getter.
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Response getter.
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Database getter.
     *
     * @return Database
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }
	
	/**
     * Sets the necessary headers to allow access from a client JavaScript application.
     * If not set, it would be blocked due to CORS policy.
     *
     * @return void
     */
	private function setHeaders(): void {
		header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: HEAD, GET, POST, PUT, PATCH, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "OPTIONS") {
            die();
        }
	}

    /**
     * If there is no error initializing the application, it will allow the router to process the request.
     *
     * @return void
     */
    public function run(): void
    {
        if($this->response->getStatusCode() == 200) {
            $this->router->resolve();
        }
    }
}