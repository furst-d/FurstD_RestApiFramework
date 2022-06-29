<?php

namespace app\utils;

use app\Application;
use app\Content;
use app\database\clause\LimitClause;
use app\database\Column;
use app\database\DatabaseContent;
use app\database\DatabaseData;
use app\database\clause\GroupByClause;
use app\database\DatabaseParameters;
use app\database\Join;
use app\database\clause\OrderByClause;
use app\database\Reference;
use app\database\clause\WhereClause;
use app\enum\ResponseFormat;
use app\enum\RestMethod;
use app\http\HttpData;
use app\http\HttpParameter;

/**
 * Class for parsing data from config.yaml
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class ConfigParser
{
    /**
     * Checks and returns data from config.yaml.
     * Returns false if data are incorrect.
     *
     * @param array $val
     * @param RestMethod $method
     * @return DatabaseData|HttpData|false
     */
    public function readData(array $val, RestMethod $method): DatabaseData|HttpData|false
    {
        switch($val['type']) {
            case "DB":
                $data = $this->readDatabaseData($val);
                break;
            case "HTTP":
                $data = $this->readHttpData($val, $method);
                break;
            default:
                return false;
        }
        return $data;
    }

    /**
     * Checks and returns database data from config.yaml.
     * Returns false if data are incorrect.
     *
     * @param array $val
     * @return DatabaseData|false
     */
    private function readDatabaseData(array $val): DatabaseData|false
    {
        $adapter = Application::$app->getDatabase()->getAdapter();
        $tableName = $val['table'];
        $columns = $this->readColumns($val);
        $joins = $this->readJoins($val);
        $parameters = $this->readDatabaseParameters($val);
        $content = $this->readDatabaseContent($val);

        if(!isset($tableName)) {
            Application::$app->getResponse()->setStatusCode(400);
            Application::$app->getResponse()->setStatusText("Missing table name in config.yaml when using database");
            return false;
        }

        if(!is_array($columns) || !is_array($joins) || !$parameters || !is_array($content)) {
            return false;
        }

        return new DatabaseData($adapter, $tableName, $columns, $joins, $parameters, $content);
    }

    /**
     * Checks and returns HTTP data from config.yaml.
     * Returns false if data are incorrect.
     *
     * @param array $val
     * @param RestMethod $method
     * @return HttpData|false
     */
    private function readHttpData(array $val, RestMethod $method): HttpData|false
    {
        $uri = $val['url'];
        $responseFormat = ResponseFormat::resolve($val['response']['format']);
        $parameters = $this->readHttpParameters($val);
        $headers = $this->readHttpHeaders($val);
        $content = $this->readContent($val);

        if(!$responseFormat || !is_array($parameters) || !is_array($content)) {
            return false;
        }

        return new HttpData($method, $uri, $responseFormat, $parameters, $content, $headers);
    }

    /**
     * Checks and returns array of columns from config.yaml when using database data.
     * Returns false if data are incorrect.
     *
     * @param array $val
     * @return array|false
     */
    private function readColumns(array $val): array|false {
        $columns = [];

        if(!isset($val['columns'])) {
            return $columns;
        }

        foreach($val['columns'] as $column) {
            if(is_array($column)) {
                $columnName = key($column);
                $column = $column[$columnName];
                $columns[] = new Column($columnName, $column['alias'] ?? null, $column['function'] ?? null);
            } else {
                $columns[] = new Column($column, null, null);
            }
        }
        return $columns;
    }

    /**
     * Checks and returns array of joins from config.yaml when using database data.
     * Returns false if data are incorrect.
     *
     * @param array $val
     * @return array|false
     */
    private function readJoins(array $val): array|false {
        $joins = [];

        if(!isset($val['joins'])) {
            return $joins;
        }

        foreach($val['joins'] as $join) {
            $table = key($join);
            $join = $join[$table];
            $type = $join['type'] ?? null;
            $column = $join['column'] ?? null;
            $primaryKey = $join['pk'] ?? null;

            if(!isset($table) || !isset($type) || !isset($column) || !isset($primaryKey)) {
                Application::$app->getResponse()->setStatusCode(400);
                Application::$app->getResponse()->setStatusText("Wrong join structure in config.yaml");
                return false;
            }

            $joins[] = new Join($table, $type, $column, $primaryKey);
        }
        return $joins;
    }

    /**
     * Checks and returns array of content from config.yaml when using HTTP data.
     * Returns false if data are incorrect.
     *
     * @param array $val
     * @return array|false
     */
    private function readContent(array $val): array|false
    {
        $contents = [];

        if(isset($val['content'])) {
            foreach($val['content'] as $content) {
                $name = key($content);
                $content = $content[$name];
                $type = $content['type'];
                $required = $content['required'];

                if(!isset($name) || !isset($type) || !isset($required)) {
                    Application::$app->getResponse()->setStatusCode(400);
                    Application::$app->getResponse()->setStatusText("Wrong content structure in config.yaml");
                    return false;
                }

                $contents[] = new Content($name, $type, $required, $content['hash'] ?? null);
            }
        }

        return $contents;
    }

    /**
     * Checks and returns array of content from config.yaml when using database data.
     * Returns false if data are incorrect.
     *
     * @param array $val
     * @return array|false
     */
    private function readDatabaseContent(array $val): array|false
    {
        $contents = [];

        if(isset($val['content'])) {
            foreach($val['content'] as $content) {
                $name = key($content);
                $content = $content[$name];
                $type = $content['type'];
                $required = $content['required'];

                $reference = null;

                if(is_array($content['refers'] ?? null)) {
                    $tableRef = $content['refers']['table'];
                    $colRef = $content['refers']['column'];
                    $pkRef = $content['refers']['pk'];

                    if(!isset($tableRef, $colRef, $pkRef)) {
                        Application::$app->getResponse()->setStatusCode(400);
                        Application::$app->getResponse()->setStatusText("Wrong content reference in config.yaml");
                        return false;
                    }

                    $reference = new Reference($tableRef, $colRef, $pkRef);
                }

                if(!isset($name) || !isset($type) || !isset($required)) {
                    Application::$app->getResponse()->setStatusCode(400);
                    Application::$app->getResponse()->setStatusText("Wrong content structure in config.yaml");
                    return false;
                }

                $contents[] = new DatabaseContent($name, $type, $required, $content['hash'] ?? null, $content['represents'] ?? null, $reference);
            }
        }

        return $contents;
    }

    /**
     * Checks and returns DatabaseParameters from config.yaml when using database data.
     * Returns false if data are incorrect.
     *
     * @param array $val
     * @return DatabaseParameters|false
     */
    private function readDatabaseParameters(array $val): DatabaseParameters|false
    {
        $databaseParameter = new DatabaseParameters();

        if(isset($val['parameters'])) {
            $whereClauses = [];

            foreach($val['parameters'] as $parameter) {
                $values = $this->getParameterValues($parameter);
                $name = $values['name'];
                $editable = $values['editable'];
                $location = $values['location'];
                $default = $values['default'];

                if(!$this->checkParameterData($name, $editable, $location, $default)) {
                    return false;
                }

                if(!$this->setDatabaseParameters($databaseParameter, $values, $parameter, $whereClauses)) {
                    return false;
                }
            }
            $databaseParameter->setWhereClauses($whereClauses);
        }
        return $databaseParameter;
    }

    /**
     * Checks and returns array of HttpParameters from config.yaml when using HTTP requests.
     * Returns false if data are incorrect.
     *
     * @param array $val
     * @return array|false
     */
    private function readHttpParameters(array $val): array|false
    {
        $parameters = [];

        if(isset($val['parameters'])) {
            foreach($val['parameters'] as $parameter) {
                $values = $this->getParameterValues($parameter);
                $name = $values['name'];
                $editable = $values['editable'];
                $location = $values['location'];
                $default = $values['default'];
                $required = $values['required'];
                $represents = $values['represents'];
                $type = $this->checkType($parameter, $editable, $name, $default);

                if(!$this->checkParameterData($name, $editable, $location, $default) || $type == -1) {
                    return false;
                }

                $parameters[$name] = new HttpParameter($name, $editable, $location, $required, $represents, $type, $default);
            }
        }
        return $parameters;
    }

    /**
     * Reads and returns array of headers from config.yaml when using HTTP requests.
     *
     * @param array $val
     * @return array
     */
    private function readHttpHeaders(array $val): array
    {
        $headers = [];

        if(isset($val['headers'])) {
            foreach($val['headers'] as $header) {
                $name = key($header);
                $headers[] = $name . ": " . $header[$name];
            }
        }

        return $headers;
    }

    /**
     * Checks and returns type from parameter.
     *
     * @param $parameter
     * @param $editable
     * @param $name
     * @param $default
     * @return string|int|null
     */
    private function checkType($parameter, $editable, $name, $default): string|null|int {
        $type = $parameter['type'] ?? null;
        if($editable == 1 && (!isset($type) || ($default != null && strcasecmp(gettype($default), $type) != 0))) {
            Application::$app->getResponse()->setStatusCode(400);
            if(!isset($type)) {
                Application::$app->getResponse()->setStatusText("Missing type declaration in '$name'");
            }
            else if($default != null && strcasecmp(gettype($default), $type) != 0) {
                Application::$app->getResponse()->setStatusText("Default value in '$name' must be same type as provided type");
            }
            return -1;
        }
        return $type;
    }

    /**
     * Sets database parameters depending on config.yaml.
     * Returns true if the parsing was successful, otherwise returns false.
     *
     * @param DatabaseParameters $databaseParameter
     * @param array $values
     * @param array $parameter
     * @param array $whereClauses
     * @return bool
     */
    private function setDatabaseParameters(DatabaseParameters $databaseParameter, array $values, array $parameter, array &$whereClauses): bool
    {
        $name = $values['name'];
        $editable = $values['editable'];
        $location = $values['location'];
        $required = $values['required'];
        $represents = $values['represents'];
        $default = $values['default'];

        switch ($represents ?? $name) {
            case "group by":
                if($data = $this->readGroupBy($parameter, $name, $editable, $location, $required, $represents, $default)) {
                    $databaseParameter->setGroupByClause($data);
                }
                break;
            case "order by":
                if($data = $this->readOrderBy($parameter, $name, $editable, $location, $required, $represents, $default)) {
                    $databaseParameter->setOrderByClause($data);
                }
                break;
            case "limit":
                if($data = new LimitClause($name, $editable, $location, $required, $represents, $default)) {
                    $databaseParameter->setLimitClause($data);
                }
                break;
            default:
                if($data = $this->readWhereClause($parameter, $name, $editable, $location, $required, $represents, $default)) {
                    $whereClauses[$name] = $data;
                }
                break;
        }

        if(!$data) {
            return false;
        }
        return true;
    }

    /**
     * Returns array of parameter attributes.
     *
     * @param array $parameter
     * @return array
     */
    private function getParameterValues(array &$parameter): array {
        $values = [];
        $values['name'] = key($parameter) ?? null;
        $parameter = $parameter[$values['name']] ?? null;
        $values['editable'] = $parameter['editable'] ?? null;
        $values['location'] = $parameter['location'] ?? null;
        $values['required'] = $parameter['required'] ?? null;
        $values['represents'] = $parameter['represents'] ?? null;
        $values['default'] = $parameter['default'] ?? null;
        return $values;
    }

    /**
     * Reads ORDER BY data.
     * Returns false if data are incorrect.
     *
     * @param array $parameter
     * @param $name
     * @param $editable
     * @param $location
     * @param $required
     * @param $represents
     * @param $default
     * @return OrderByClause|false
     */
    private function readOrderBy(array $parameter, $name, $editable, $location, $required, $represents, $default): OrderByClause|false {
        $order = $parameter['order'] ?? "ASC";

        if(!is_array($default)) {
            $temp = $default;
            $default = [];
            $default[] = $temp;
        }

        return new OrderByClause($name, $editable, $location, $required, $represents, $default, $order);
    }

    /**
     * Reads GROUP BY data.
     * Returns false if data are incorrect.
     *
     * @param array $parameter
     * @param $name
     * @param $editable
     * @param $location
     * @param $required
     * @param $represents
     * @param $default
     * @return GroupByClause|false
     */
    private function readGroupBy(array $parameter, $name, $editable, $location, $required, $represents, $default): GroupByClause|false {
        $havingClauses = [];

        foreach ($parameter['having'] as $clause) {
            $values = $this->getParameterValues($clause);
            $havName = $values['name'];
            $havEditable = $values['editable'];
            $havLocation = $values['location'];
            $havRequired = $values['required'];
            $havRepresents = $values['represents'];
            $havDefault = $values['default'];

            if(!$this->checkParameterData($havName, $havEditable, $havLocation, $havDefault)) {
                return false;
            }

            $havingClauses[] = $this->readWhereClause($clause, $havName, $havEditable, $havLocation, $havRequired, $havRepresents, $havDefault);
        }

        return new GroupByClause($name, $editable, $location, $required, $represents, $default, $havingClauses);
    }

    /**
     * Reads WHERE clause data.
     * Returns false if data are incorrect.
     *
     * @param array $parameter
     * @param $name
     * @param $editable
     * @param $location
     * @param $required
     * @param $represents
     * @param $default
     * @return WhereClause|false
     */
    private function readWhereClause(array $parameter, $name, $editable, $location, $required, $represents, $default): WhereClause|false {
        $type = $parameter['type'] ?? null;
        $function = $parameter['function'] ?? null;
        $operator = $parameter['operator'] ?? null;

        if($editable == 1 && (!isset($type) || ($default != null && strcasecmp(gettype($default), $type) != 0)) || !isset($operator)) {
            Application::$app->getResponse()->setStatusCode(400);
            if(!isset($type)) {
                Application::$app->getResponse()->setStatusText("Missing type declaration in '$name'");
            }
            else if($default != null && strcasecmp(gettype($default), $type) != 0) {
                Application::$app->getResponse()->setStatusText("Default value in '$name' must be same type as provided type");
            }
            else if(!isset($operator)) {
                Application::$app->getResponse()->setStatusText("Missing operator declaration in '$name'");
            }
            return false;
        }

        return new WhereClause($name, $editable, $location, $required, $represents, $type, $function, $operator, $default);
    }

    /**
     * Checks common parameter data.
     * Returns true if data are correct or false if data are incorrect.
     *
     * @param $name
     * @param $editable
     * @param $location
     * @param $default
     * @return bool
     */
    private function checkParameterData($name, $editable, $location, $default): bool {
        if(($editable == 1 && ($location != "query" && $location != "path")) || (!$editable && !$default)) {
            Application::$app->getResponse()->setStatusCode(400);
            if($editable == 1 && ($location != "query" && $location != "path")) {
                if($location == null) {
                    Application::$app->getResponse()->setStatusText("You must specify location value in '$name' when editable is true");
                } else {
                    Application::$app->getResponse()->setStatusText("Location in '$name' must be 'query' or 'path'");
                }
            }
            else if(!$editable && !$default) {
                Application::$app->getResponse()->setStatusText("You must specify default value in '$name' when editable is false");
            }

            return false;
        }
        return true;
    }

    /**
     * Returns array of roles if there is an authorization settings otherwise returns an empty array.
     *
     * @param array $val
     * @return array
     */
    public function readAuthRoles(array $val): array
    {
        $roles = [];

        if(isset($val['authorization'])) {
            $roles = $val['authorization'];
        }

        return $roles;
    }
}