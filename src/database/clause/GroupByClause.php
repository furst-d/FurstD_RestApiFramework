<?php

namespace app\database\clause;

use app\Parameter;

/**
 * Represents GROUP BY SQL clause.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class GroupByClause extends Parameter
{
    private ?string $default;
    private ?array $havingClause;

    /**
     * GroupByClause constructor.
     *
     * @param string $name
     * @param bool $editable
     * @param string|null $location
     * @param bool|null $required
     * @param string|null $represents
     * @param string|null $default
     * @param array|null $havingClause
     */
    public function __construct(string $name, bool $editable, ?string $location, ?bool $required, ?string $represents,
                                ?string $default, ?array $havingClause)
    {
        parent::__construct($name, $editable, $location, $required, $represents);
        $this->default = $default;
        $this->havingClause = $havingClause;
    }

    /**
     * Default getter.
     *
     * @return string|null
     */
    public function getDefault(): ?string
    {
        return $this->default;
    }

    /**
     * HavingClause getter.
     *
     * @return array|null
     */
    public function getHavingClause(): ?array
    {
        return $this->havingClause;
    }
}