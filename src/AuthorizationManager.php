<?php

namespace app;

use app\router\Route;
use mysqli;

/**
 * Used to verify user permissions.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class AuthorizationManager
{
    private Route $route;
    private mysqli $connection;

    /**
     * AuthorizationManager constructor.
     *
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
        $this->connection = Application::$app->getDatabase()->getConnection();
    }

    /**
     * Checks if authorization method is selected and performs authorization verification.
     *
     * @return bool
     */
    public function checkAuthorization(): bool
    {
        if(empty($this->route->getAuthRoles())) {
            return true;
        }

        if(empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
            header("WWW-Authenticate: Basic realm=\"private\"");
            Application::$app->getResponse()->setStatusCode(401);
            Application::$app->getResponse()->setStatusText("Unauthorized");
            return false;
        }

        return $this->checkRoles();

    }

    /**
     * Checks if user with inputted credentials has one of the allowed roles.
     *
     * @return bool
     */
    private function checkRoles(): bool {
        $result = $this->connection->query($this->getQuery())->fetch_assoc();
        if($result['count'] == 0) {
            Application::$app->getResponse()->setStatusCode(401);
            Application::$app->getResponse()->setStatusText("Wrong credentials or insufficient authorization");
            return false;
        }
        return true;
    }

    /**
     * Builds a SQL query for selecting count of authorized users.
     *
     * @return string
     */
    private function getQuery(): string
    {
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = hash("sha256", $_SERVER['PHP_AUTH_PW']);
        $query = "SELECT count(user_id) 'count' FROM auth_users JOIN auth_roles using(role_id) WHERE username = '$username' AND password = '$password' AND name IN (";
        foreach ($this->route->getAuthRoles() as $role) {
            $query .= "'$role', ";
        }
        $query = substr($query, 0, strlen($query) - 2);
        $query .= ")";
        return $query;
    }
}