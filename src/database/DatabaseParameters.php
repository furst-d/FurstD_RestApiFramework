<?php

namespace app\database;

use app\database\clause\GroupByClause;
use app\database\clause\LimitClause;
use app\database\clause\OrderByClause;

/**
 * Represents database parameter.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class DatabaseParameters
{
    private ?array $whereClauses;
    private ?GroupByClause $groupByClause;
    private ?OrderByClause $orderByClause;
    private ?LimitClause $limitClause;

    /**
     * DatabaseParameters constructor.
     */
    public function __construct()
    {
        $this->whereClauses = [];
        $this->groupByClause = null;
        $this->orderByClause = null;
        $this->limitClause = null;
    }


    /**
     * WhereClauses getter.
     *
     * @return array|null
     */
    public function getWhereClauses(): ?array
    {
        return $this->whereClauses;
    }

    /**
     * WhereClauses setter.
     *
     * @param array|null $whereClauses
     */
    public function setWhereClauses(?array $whereClauses): void
    {
        $this->whereClauses = $whereClauses;
    }

    /**
     * GroupByClause getter.
     *
     * @return GroupByClause|null
     */
    public function getGroupByClause(): ?GroupByClause
    {
        return $this->groupByClause;
    }

    /**
     * GroupByClause setter.
     *
     * @param GroupByClause|null $groupByClause
     */
    public function setGroupByClause(?GroupByClause $groupByClause): void
    {
        $this->groupByClause = $groupByClause;
    }


    /**
     * OrderByClause getter.
     *
     * @return OrderByClause|null
     */
    public function getOrderByClause(): ?OrderByClause
    {
        return $this->orderByClause;
    }

    /**
     * OrderByClause setter.
     *
     * @param OrderByClause|null $orderByClause
     */
    public function setOrderByClause(?OrderByClause $orderByClause): void
    {
        $this->orderByClause = $orderByClause;
    }

    /**
     * LimitClause getter.
     *
     * @return LimitClause|null
     */
    public function getLimitClause(): ?LimitClause
    {
        return $this->limitClause;
    }

    /**
     * LimitClause setter.
     *
     * @param LimitClause|null $limitClause
     */
    public function setLimitClause(?LimitClause $limitClause): void
    {
        $this->limitClause = $limitClause;
    }
}