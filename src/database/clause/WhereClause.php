<?php

namespace app\database\clause;

use app\Parameter;

/**
 * Represents WHERE SQL clause.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class WhereClause extends Parameter
{
    private string $type;
    private ?string $function;
    private string $operator;
    private mixed $default;

    /**
     * WhereClause constructor.
     *
     * @param string $name
     * @param bool $editable
     * @param string|null $location
     * @param bool|null $required
     * @param string|null $represents
     * @param string $type
     * @param string|null $function
     * @param string $operator
     * @param mixed $default
     */
    public function __construct(string $name, bool $editable, ?string $location, ?bool $required, ?string $represents,
                                string $type, ?string $function, string $operator, mixed $default)
    {
        parent::__construct($name, $editable, $location, $required, $represents);
        $this->type = $type;
        $this->function = $function;
        $this->operator = $operator;
        $this->default = $default;
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
     * @return string|null
     */
    public function getFunction(): ?string
    {
        return $this->function;
    }

    /**
     * Operator getter.
     *
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Default getter.
     *
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }
}