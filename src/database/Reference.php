<?php

namespace app\database;

/**
 * Represents reference to another column when using multiple tables via JOIN.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class Reference
{
    private string $table;
    private string $column;
    private string $primaryKey;

    /**
     * Reference constructor.
     *
     * @param string $table
     * @param string $column
     * @param string $primaryKey
     */
    public function __construct(string $table, string $column, string $primaryKey)
    {
        $this->table = $table;
        $this->column = $column;
        $this->primaryKey = $primaryKey;
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
     * Column getter.
     *
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * PrimaryKey getter.
     *
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }
}