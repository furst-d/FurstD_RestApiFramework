<?php

namespace app\database\clause;

use app\Parameter;

/**
 * Represents ORDER BY SQL clause.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class OrderByClause extends Parameter
{
    private ?array $default;
    private ?string $order;

    /**
     * OrderByClause constructor.
     *
     * @param string $name
     * @param bool $editable
     * @param string|null $location
     * @param bool|null $required
     * @param string|null $represents
     * @param array|null $default
     * @param string $order
     */
    public function __construct(string $name, bool $editable, ?string $location, ?bool $required, ?string $represents,
                                ?array $default, string $order)
    {
        parent::__construct($name, $editable, $location, $required, $represents);
        $this->default = $default;
        $this->order = $order;
    }

    /**
     * Default getter.
     *
     * @return array|null
     */
    public function getDefault(): ?array
    {
        return $this->default;
    }

    /**
     * @return string|null
     */
    public function getOrder(): ?string
    {
        return $this->order;
    }
}