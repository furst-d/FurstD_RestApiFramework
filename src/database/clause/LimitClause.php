<?php

namespace app\database\clause;

use app\Parameter;

/**
 * Represents LIMIT SQL clause.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class LimitClause extends Parameter
{
    private ?int $default;

    /**
     * LimitClause constructor.
     *
     * @param string $name
     * @param bool $editable
     * @param string|null $location
     * @param bool|null $required
     * @param string|null $represents
     * @param int|null $default
     */
    public function __construct(string $name, bool $editable, ?string $location, ?bool $required,
                                ?string $represents, ?int $default)
    {
        parent::__construct($name, $editable, $location, $required, $represents);
        $this->default = $default;
    }

    /**
     * Default getter.
     *
     * @return int|null
     */
    public function getDefault(): ?int
    {
        return $this->default;
    }
}