<?php

namespace app\database;

use app\Application;
use app\database\clause\GroupByClause;
use app\database\clause\LimitClause;
use app\database\clause\OrderByClause;
use app\enum\DatabaseAdapter;
use app\Request;
use app\Response;
use app\router\Route;
use ValueError;

/**
 * Class used for composing queries.
 */
class SQLBuilder
{
    /**
     * Creates SELECT SQL string depending on parameters.
     * Returns false if inserted data are incorrect.
     *
     * @param Request $request
     * @param Response $response
     * @param Route $route
     * @return string|false
     */
    public function buildGetQuery(Request $request, Response $response, Route $route): string|false {
        $sql = "SELECT ";
        $databaseData = $route->getData();
        if($sql = $this->addColumns($sql, $databaseData->getColumns())) {
            $sql .= " FROM " . $databaseData->getTable() . " ";
            $sql = $this->addJoins($sql, $databaseData->getJoins());
            if($sql = $this->addParameters($sql, $databaseData->getParameters(), $databaseData->getAdapter(), $request, $response)) {
                return $sql;
            }
        }
        return false;
    }

    /**
     * Creates INSERT SQL string depending on parameters.
     * Returns false if inserted data are incorrect.
     *
     * @param Request $request
     * @param Response $response
     * @param Route $route
     * @return string|false
     */
    public function buildPostQuery(Request $request, Response $response, Route $route): string|false {
        $content = json_decode($request->getContent(), true);
        $databaseData = $route->getData();
        $sql = "INSERT INTO " . $databaseData->getTable();

        $columns = [];
        $values = [];
        foreach ($databaseData->getContent() as $column) {
            $contentValue = $this->getContentValue($content, $column, $response);

            if($contentValue == -1 || !$this->checkRequiredContentExists($column, $contentValue, $response)
                    || ($contentValue != null && !$this->checkType($column, $response, $contentValue))) {
                return false;
            }

            if($contentValue != null) {
                $name = $column->getRepresentation() == null ? $column->getName() : $column->getRepresentation();
                $columns[] = $name;
                if($column->getReference() != null) {
                    $values[] = "(SELECT " . $column->getReference()->getPrimaryKey()
                        . " FROM " . $column->getReference()->getTable()
                        . " WHERE " . $column->getReference()->getColumn()
                        . " = '" . $contentValue . "')";
                } else {
                    $values[] = "'$contentValue'";
                }
            }
        }

        $sql .= $this->addInsertColumns($columns);
        $sql .= $this->addInsertValues($values);
        return $sql;
    }

    /**
     * Creates UPDATE SQL string depending on parameters.
     * Returns false if inserted data are incorrect.
     *
     * @param Request $request
     * @param Response $response
     * @param Route $route
     * @return string|false
     */
    public function buildPutQuery(Request $request, Response $response, Route $route): string|false {
        $content = json_decode($request->getContent(), true);
        $databaseData = $route->getData();
        $sql = "UPDATE " . $databaseData->getTable() . " SET ";

        foreach ($databaseData->getContent() as $column) {
            $contentValue = $this->getContentValue($content, $column, $response);

            if($contentValue == -1 || !$this->checkRequiredContentExists($column, $contentValue, $response)) {
                return false;
            }

            if($contentValue != null) {
                if(!$this->checkType($column, $response, $contentValue)) {
                    return false;
                }

                $name = $column->getRepresentation() == null ? $column->getName() : $column->getRepresentation();
                $sql .= "$name = '$contentValue', ";
            }
        }
        $sql = substr($sql, 0, strlen($sql) - 2);
        return $this->addParameters($sql, $databaseData->getParameters(), $databaseData->getAdapter(), $request, $response)
            ? $this->addParameters($sql, $databaseData->getParameters(), $databaseData->getAdapter(), $request, $response) : false;
    }

    /**
     * Creates DELETE SQL string depending on parameters.
     * Returns false if inserted data are incorrect.
     *
     * @param Request $request
     * @param Response $response
     * @param Route $route
     * @return string|false
     */
    public function buildDeleteQuery(Request $request, Response $response, Route $route): string|false {
        $databaseData = $route->getData();
        $sql = "DELETE FROM " . $databaseData->getTable();
        $sql = $this->addJoins($sql, $databaseData->getJoins());
        if($sql = $this->addParameters($sql, $databaseData->getParameters(), $databaseData->getAdapter(), $request, $response)) {
            return $sql;
        }
        return false;
    }

    /**
     * Adds column names as part of the SQL INSERT query.
     *
     * @param array $columns
     * @return string
     */
    private function addInsertColumns(array $columns): string {
        $sql = " (";
        for($i = 0; $i < sizeof($columns); $i++) {
            $sql .= "$columns[$i], ";
        }

        $sql = substr($sql, 0, strlen($sql) - 2);
        $sql .= ")";
        return $sql;
    }

    /**
     * Adds content values as part of the SQL INSERT query.
     *
     * @param array $values
     * @return string
     */
    private function addInsertValues(array $values): string {
        $sql = " VALUES (";
        for($i = 0; $i < sizeof($values); $i++) {
            $sql .= "$values[$i], ";
        }

        $sql = substr($sql, 0, strlen($sql) - 2);
        $sql .= ")";
        return $sql;
    }

    /**
     * Resolve clause and returns formatted parameter name for the following uses in SQL query.
     *
     * @param $clause
     * @return string
     */
    private function getParamName($clause): string {
        $paramName = $clause->getRepresents() == null
            ? $clause->getName() : $clause->getRepresents();
        return $clause->getFunction() == null
            ? $paramName : $clause->getFunction() . "(" . $paramName . ")";
    }

    /**
     * Checks if the required content value exists.
     *
     * @param DatabaseContent $column
     * @param string|null $contentValue
     * @param Response $response
     * @return bool
     */
    private function checkRequiredContentExists(DatabaseContent $column, ?string $contentValue, Response $response): bool {
        if($contentValue == null && $column->isRequired()) {
            $response->setStatusCode(400);
            $response->setStatusText("Required value '" . $column->getName() . "' not found");
            return false;
        }
        return true;
    }

    /**
     * Checks if the column value can be converted to defined type.
     *
     * @param $column
     * @param Response $response
     * @param string $value
     * @return bool
     */
    private function checkType($column, Response $response, string $value): bool {
        $temp = $value;
        settype($value, $column->getType());
        if($temp != $value) {
            $response->setStatusCode(400);
            $response->setStatusText("Parameter '"
                . $column->getName() . "' must be " . $column->getType());
            return false;
        }
        return true;
    }

    /**
     * Returns content value.
     * If there is a hash set, returns hashed values.
     * Returns -1 if hash algorithm is not supported.
     *
     * @param array $content
     * @param DatabaseContent $column
     * @param Response $response
     * @return string|int|null
     */
    private function getContentValue(array $content, DatabaseContent $column, Response $response): string|int|null
    {
        $contentValue = $content[$column->getName()] ?? null;
        $hash = $column->getHash();
        if($hash != null) {
            try {
                $contentValue = hash($column->getHash(), $contentValue);
            } catch (ValueError) {
                $response->setStatusCode(400);
                $response->setStatusText("Hash algorithm '" . $column->getHash() . "' is not supported");
                return -1;
            }
        }
        return $contentValue;
    }

    /**
     * Adds columns into the SQL query string.
     * Returns false if data contains no columns.
     *
     * @param string $sql
     * @param array $columns
     * @return string|false
     */
    private function addColumns(string $sql, array $columns): string|false
    {
        if(!empty($columns)) {
            foreach ($columns as $column) {
                $sql .= $column->getFunction() != null
                    ? $column->getFunction() . "(" . $column->getName() . ")"
                    : $column->getName();
                $sql .= $column->getAlias() != null ? " '" . $column->getAlias() . "', " : ", ";
            }
            return substr($sql, 0, strlen($sql) - 2);
        } else {
            Application::$app->getResponse()->setStatusCode(400);
            Application::$app->getResponse()->setStatusText("You have to specify columns when using GET method");
        }

        return false;
    }

    /**
     * Adds joins into the SQL query string.
     *
     * @param string $sql
     * @param array $joins
     * @return string
     */
    private function addJoins(string $sql, array $joins): string
    {
        foreach ($joins as $join) {
            $sql .=  strtoupper($join->getType()) . " " . $join->getTable()
                . " ON " . $join->getColumn() . " = " . $join->getPrimaryKey() . " ";
        }
        return empty($joins) ? $sql : substr($sql, 0, strlen($sql) - 1);
    }

    /**
     * Adds parameters into the SQL query string.
     * Returns false if data are incorrect.
     *
     * @param string $sql
     * @param DatabaseParameters $parameters
     * @param DatabaseAdapter $adapter
     * @param Request $request
     * @param Response $response
     * @return string|false
     */
    private function addParameters(string $sql, DatabaseParameters $parameters, DatabaseAdapter $adapter, Request $request, Response $response): string|false
    {
        $result = $this->addWhereClauses($parameters->getWhereClauses(), $request, $response);
        if(empty($result) || $result != -1) {
            $sql .= $result;
            $result = $this->addGroupByClause($parameters->getGroupByClause(), $request, $response);
            if(empty($result) || $result != -1) {
                $sql .= $result . $this->addOrderByClause($parameters->getOrderByClause());
                $result = $this->addLimitClause($adapter, $parameters->getLimitClause(), $request);
                if(empty($result) || $result != -1) {
                    $sql .= $result;
                    return $sql;
                }
            }
        }
        return false;
    }

    /**
     * Adds Where clauses into the SQL query string.
     * Returns false if parameter was not found or if parameter has wrong type.
     *
     * @param array $whereClauses
     * @param Request $request
     * @param Response $response
     * @return string|int
     */
    private function addWhereClauses(array $whereClauses, Request $request, Response $response): string|int {
        $sql = "";
        if(!empty($whereClauses)) {
            $sql = " WHERE ";
            foreach ($whereClauses as $whereClause) {
                if($param = $this->getParameter($whereClause, $request)) {
                    if(!$this->checkType($whereClause, $response, $param)) {
                        return -1;
                    }

                    $sql .= $this->getParamName($whereClause) . " "
                        . $whereClause->getOperator() . " '" . $param . "' AND ";
                } else {
                    return -1;
                }
            }
        }
        return empty($whereClauses) ? $sql : substr($sql, 0, strlen($sql) - 5);
    }

    /**
     * Adds Group By clause into the SQL query string.
     * Returns false if HAVING parameter was not found or if HAVING parameter has wrong type.
     *
     * @param GroupByClause|null $groupByClause
     * @param Request $request
     * @param Response $response
     * @return string|int
     */
    private function addGroupByClause(?GroupByClause $groupByClause, Request $request, Response $response): string|int {
        $sql = "";
        if($groupByClause != null) {
            $sql .= " GROUP BY " . $groupByClause->getDefault();
            if($groupByClause->getHavingClause() != null) {
                $having = $this->addWhereClauses($groupByClause->getHavingClause(), $request, $response);
                if(!$having) {
                    return -1;
                }
                $sql .= str_replace("WHERE", "HAVING", $having);
            }
        }
        return $sql;
    }

    /**
     * Adds Order By clause into the SQL query string.
     *
     * @param OrderByClause|null $orderByClause
     * @return string
     */
    private function addOrderByClause(?OrderByClause $orderByClause): string {
        $sql = "";
        if($orderByClause != null) {
            $sql .= " ORDER BY ";
            foreach ($orderByClause->getDefault() as $column) {
                $sql .= $column . " " . $orderByClause->getOrder() . ", ";
            }
            $sql = substr($sql, 0, strlen($sql) - 2);
        }
        return $sql;
    }

    /**
     * Adds Limit clause into the SQL query string.
     * Returns false if parameter was not found or if parameter has wrong type.
     * The function is also used to demonstrate how to solve possibly several database adapters.
     *
     * @param DatabaseAdapter $adapter
     * @param LimitClause|null $limitClause
     * @param Request $request
     * @return string|int
     */
    private function addLimitClause(DatabaseAdapter $adapter, ?LimitClause $limitClause, Request $request): string|int {
        $sql = "";
        if($limitClause != null) {
            if($param = $this->getParameter($limitClause, $request)) {
                switch ($adapter) {
                    case DatabaseAdapter::MYSQL:
                        $sql .= " LIMIT " . $param;
                        break;
                    case DatabaseAdapter::ORACLE:
                        $sql .= " FETCH NEXT " . $param . " ROWS ONLY";
                        break;
                    case DatabaseAdapter::POSTGRESQL:
                        $sql .= " LIMIT " . $param . " OFFSET 0";
                        break;
                }
            } else {
                return -1;
            }
        }
        return $sql;
    }

    /**
     * Returns parameter depending on inserted configuration data.
     * Returns false if parameter was not found.
     *
     * @param $clause
     * @param Request $request
     * @return string|false
     */
    private function getParameter($clause, Request $request): string|false {
        $param = $request->getParameter($clause, $request);

        if($clause->getRequired() && $param == null) {
            Application::$app->getResponse()->setStatusCode(400);
            Application::$app->getResponse()->setStatusText("Required parameter '" .
                $clause->getName() . "' not found");
            return false;
        }
        return $param == null ? "" : $param;
    }
}