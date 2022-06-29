<?php

namespace app\database;

/**
 * Represents reference to another table when selecting multiple tables via JOIN.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class Join
{
    private string $table;
    private string $type;
    private string $column;
    private string $primaryKey;

    /**
     * Join constructor.
     *
     * @param string $table
     * @param string $type
     * @param string $column
     * @param string $primaryKey
     */
    public function __construct(string $table, string $type, string $column, string $primaryKey)
    {
        $this->table = $table;
        $this->type = $type;
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
     * Type getter.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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