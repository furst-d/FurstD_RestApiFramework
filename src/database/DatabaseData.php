<?php

namespace app\database;

use app\enum\DatabaseAdapter;

/**
 * Represents database data.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class DatabaseData
{
    private DatabaseAdapter $adapter;
    private string $table;
    private ?array $columns;
    private ?array $joins;
    private ?DatabaseParameters $parameters;
    private ?array $content;

    /**
     * DatabaseData constructor.
     *
     * @param DatabaseAdapter $adapter
     * @param string $table
     * @param array|null $columns
     * @param array|null $joins
     * @param DatabaseParameters|null $parameters
     * @param array|null $content
     */
    public function __construct(DatabaseAdapter $adapter, string $table, ?array $columns, ?array $joins, ?DatabaseParameters $parameters, ?array $content)
    {
        $this->adapter = $adapter;
        $this->table = $table;
        $this->columns = $columns;
        $this->joins = $joins;
        $this->parameters = $parameters;
        $this->content = $content;
    }

    /**
     * @return DatabaseAdapter
     */
    public function getAdapter(): DatabaseAdapter
    {
        return $this->adapter;
    }

    /**
     * Table getter.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Columns getter.
     *
     * @return array|null
     */
    public function getColumns(): ?array
    {
        return $this->columns;
    }

    /**
     * Joins getter.
     *
     * @return array|null
     */
    public function getJoins(): ?array
    {
        return $this->joins;
    }

    /**
     * Parameters getter.
     *
     * @return DatabaseParameters|null
     */
    public function getParameters(): ?DatabaseParameters
    {
        return $this->parameters;
    }

    /**
     * Content getter.
     *
     * @return array|null
     */
    public function getContent(): ?array
    {
        return $this->content;
    }
}