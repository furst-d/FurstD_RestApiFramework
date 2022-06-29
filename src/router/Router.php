<?php

namespace app\router;

use app\AuthorizationManager;
use app\controller\AbstractController;
use app\database\clause\WhereClause;
use app\database\DatabaseData;
use app\enum\RequestType;
use app\enum\RestMethod;
use app\http\HttpData;
use app\http\HttpParameter;
use app\Request;
use app\Response;
use app\utils\ConfigParser;
use app\utils\YamlConfig;

/**
 * Class Router.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class Router
{
    private array $routes;
    private Request $request;
    private Response $response;
    private string $notFoundMessage;
    private ConfigParser $configParser;

    /**
     * Router constructor.
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->configParser = new ConfigParser();

        $this->loadFromConfig();

        $this->setNotFoundMessage('Page not found');
    }

    /**
     * Routes getter.
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Allows user to set notFoundMessage.
     *
     * @param bool|string $notFoundMessage
     * @return void
     */
    public function setNotFoundMessage(bool|string $notFoundMessage): void
    {
        $this->notFoundMessage = $notFoundMessage;
    }

    /**
     * Add new Route into routes array.
     *
     * @param Route $route
     * @return void
     */
    private function addRoute(Route $route): void
    {
        $this->routes[] = $route;
    }

    /**
     * Checks the config file.
     * If the content is OK, it gradually adds all routes to the array.
     * Otherwise, it sets the error status code and message to the response.
     *
     * @return void
     */
    private function loadFromConfig(): void
    {
        $configName = "config.yaml";
        $file = YamlConfig::loadFile($configName);
        if(!$file) {
            $this->response->setStatusText("Routes file $configName not found");
            return;
        }
        foreach ($file as $path => $methods) {
            foreach ($methods as $val) {
                $methodName = key($val);
                $val = $val[$methodName];
                $requestTypeName = $val['type'];

                if(!isset($requestTypeName)) {
                    $this->response->setStatusCode(400);
                    $this->response->setStatusText("Missing type in '$configName'");
                    return;
                }

                $method = RestMethod::resolve($methodName);
                $requestType = RequestType::resolve($requestTypeName);
                $data = $this->configParser->readData($val, $method);
                $roles = $this->configParser->readAuthRoles($val);

                if(!$method || !$requestType || !$data) {
                    return;
                }

                $controller = new AbstractController();
                $controller->setClass("RestController");
                $controller->setAction($methodName);

                $successMessage = null;
                if(isset($val['response']['message'])) {
                    $successMessage = $val['response']['message'];
                }

                $this->addRoute(new Route($path, $controller, $method, $requestType, $data, $successMessage, $roles));
            }
        }
    }

    /**
     * It loops through routes and if any match the request, calls the appropriate function in the appropriate controller.
     * In case of an error, it sets the corresponding status code and status message to the response.
     *
     * @return void
     */
    public function resolve(): void
    {
        foreach ($this->getRoutes() as $route) {
            if($this->matchExactly($route) || $this->resolvePattern($route)) {
                if($route->getSuccessMessage() != null) {
                    $this->response->setStatusText($route->getSuccessMessage());
                }

                $callback = $this->getCallback($route);
                $authManager = new AuthorizationManager($route);
                if(!$callback || !$authManager->checkAuthorization()) {
                    return;
                }

                call_user_func($callback, $this->request, $this->response, $route);
                return;
            }
        }

        $this->response->setStatusCode(404);
        $this->response->setStatusText($this->notFoundMessage);
    }

    /**
     * Creates callback to function in class from route for calling function.
     * Returns false if class or function does not exist.
     *
     * @param Route $route
     * @return callable|bool|array
     */
    private function getCallback(Route $route): callable|bool|array
    {
        $class = 'app\controller\\' . $route->getController()->getClass();
        if(!class_exists($class)) {
            $this->response->setStatusCode(400);
            $this->response->setStatusText("Class '" . $route->getController()->getClass()
                . "' does not exists");
            return false;
        }

        $controller = new $class();
        $controller->setAction($route->getController()->getAction());
        $callback = array();
        $callback[0] = $controller;
        $callback[1] = $route->getController()->getAction();

        if(!is_callable($callback)) {
            $this->response->setStatusCode(400);
            $this->response->setStatusText("Action '" . $route->getController()->getAction()
                . "' does not exists");
            return false;
        }

        return $callback;
    }

    /**
     * Checks whether the Route URL and REST method match the URL and REST method from the request.
     *
     * @param Route $route
     * @return bool
     */
    private function matchExactly(Route $route) : bool {
        return $this->request->getUrl() == $route->getPath()
            && $this->request->getRestMethod() == $route->getMethod();
    }

    /**
     * Checks if the Request URL path matches the Route pattern from config.yaml.
     *
     * @param Route $route
     * @return bool
     */
    private function resolvePattern(Route $route): bool {
        $requestURL = trim($this->request->getUrl(), '/');
        $splRequestURL = explode('/', $requestURL);

        $routeURL = trim($route->getPath(), '/');
        $splRouteURL = explode('/', $routeURL);

        $routeParams = [];
        if($this->request->getRestMethod() == $route->getMethod()
            && sizeof($splRequestURL) == sizeof($splRouteURL)) {
            for($i = 0; $i < sizeof($splRequestURL); $i++) {
                if($splRequestURL[$i] != $splRouteURL[$i]) {
                    $paramName = trim($splRouteURL[$i], '{}');
                    $paramData = $this->getParameterData($paramName, $route);
                    $paramValue = $splRequestURL[$i];
                    if(!preg_match('/^\{.*}$/', $splRouteURL[$i])
                        || !$paramData
                        || $paramData->getLocation() != "path"
                        || !settype($splRequestURL[$i], $paramData->getType())
                        || $paramValue != $splRequestURL[$i]) {
                        break;
                    }
                    $routeParams[$paramName] = $splRequestURL[$i];
                }

                if($i == sizeof($splRequestURL) - 1) {
                    $this->request->setRouteParams($routeParams);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns parameter data according to request method.
     *
     * @param string $paramName
     * @param Route $route
     * @return DatabaseData|HttpData|null
     */
    private function getParameterData(string $paramName, Route $route): WhereClause|HttpParameter|null {
        if($route->getData() instanceof DatabaseData) {
            $paramData = $route->getData()->getParameters()->getWhereClauses()[$paramName] ?? null;
        } else {
            $paramData = $route->getData()->getParameters()[$paramName] ?? null;
        }
        return $paramData;
    }
}