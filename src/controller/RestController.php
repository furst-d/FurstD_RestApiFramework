<?php

namespace app\controller;

use app\database\SQLBuilder;
use app\database\SQLManager;
use app\enum\RequestType;
use app\http\HttpBuilder;
use app\http\HttpManager;
use app\Request;
use app\Response;
use app\router\Route;

/**
 * Abstract controller procuring REST operations.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class RestController extends AbstractController
{
    private SQLBuilder $sqlBuilder;
    private HttpBuilder $httpBuilder;

    /**
     * RestController constructor.
     */
    public function __construct()
    {
        $this->sqlBuilder = new SQLBuilder();
        $this->httpBuilder = new HttpBuilder();
    }

    /**
     * Function representing GET method from REST interface.
     *
     * @param Request $request
     * @param Response $response
     * @param Route $route
     * @return void
     */
    public function GET(Request $request, Response $response, Route $route): void
    {
        if($route->getRequestType() == RequestType::DATABASE) {
            if($sql = $this->sqlBuilder->buildGetQuery($request, $response, $route)) {
                $sqlManager = new SQLManager($sql, $response, $route->getMethod());
                $sqlManager->send();
            }

        } else if($route->getRequestType() == RequestType::HTTP
                && $this->httpBuilder->buildQueryString($request, $response, $route)) {
            $httpManager = new HttpManager($route->getData(), $request, $response);
            $httpManager->send();
        }
    }

    /**
     * Function representing POST method from REST interface.
     *
     * @param Request $request
     * @param Response $response
     * @param Route $route
     * @return void
     */
    public function POST(Request $request, Response $response, Route $route): void
    {
        if($route->getRequestType() == RequestType::DATABASE) {
            if($sql = $this->sqlBuilder->buildPostQuery($request, $response, $route)) {
                $sqlManager = new SQLManager($sql, $response, $route->getMethod());
                $sqlManager->send();
            }

        } else if($route->getRequestType() == RequestType::HTTP
            && $this->httpBuilder->buildQueryString($request, $response, $route)) {
            $httpManager = new HttpManager($route->getData(), $request, $response);
            $httpManager->send();
        }
    }

    /**
     * Function representing PUT method from REST interface.
     *
     * @param Request $request
     * @param Response $response
     * @param Route $route
     * @return void
     */
    public function PUT(Request $request, Response $response, Route $route): void {
        if($route->getRequestType() == RequestType::DATABASE) {
            if($sql = $this->sqlBuilder->buildPutQuery($request, $response, $route)) {
                $sqlManager = new SQLManager($sql, $response, $route->getMethod());
                $sqlManager->send();
            }
        } else if($route->getRequestType() == RequestType::HTTP
            && $this->httpBuilder->buildQueryString($request, $response, $route)) {
            $httpManager = new HttpManager($route->getData(), $request, $response);
            $httpManager->send();
        }
    }

    /**
     * Function representing PATCH method from REST interface.
     *
     * @param Request $request
     * @param Response $response
     * @param Route $route
     * @return void
     */
    public function PATCH(Request $request, Response $response, Route $route): void {
        $this->PUT($request, $response, $route);
    }

    /**
     * Function representing DELETE method from REST interface.
     *
     * @param Request $request
     * @param Response $response
     * @param Route $route
     * @return void
     */
    public function DELETE(Request $request, Response $response, Route $route): void {
        if($route->getRequestType() == RequestType::DATABASE) {
            if($sql = $this->sqlBuilder->buildDeleteQuery($request, $response, $route)) {
                $sqlManager = new SQLManager($sql, $response, $route->getMethod());
                $sqlManager->send();
            }
        } else if($route->getRequestType() == RequestType::HTTP
            && $this->httpBuilder->buildQueryString($request, $response, $route)) {
            $httpManager = new HttpManager($route->getData(), $request, $response);
            $httpManager->send();
        }
    }
}