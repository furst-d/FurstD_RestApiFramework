<?php

namespace app\database;

/**
 * Represents column when selecting from database.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class Column
{
    private string $name;
    private ?string $alias;
    private ?string $function;

    /**
     * Column constructor.
     *
     * @param string $name
     * @param string|null $alias
     * @param string|null $function
     */
    public function __construct(string $name, ?string $alias, ?string $function)
    {
        $this->name = $name;
        $this->alias = $alias;
        $this->function = $function;
    }

    /**
     * Name getter.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Alias getter.
     *
     * @return string|null
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * Function getter.
     *
     * @return string|null
     */
    public function getFunction(): ?string
    {
        return $this->function;
    }
}