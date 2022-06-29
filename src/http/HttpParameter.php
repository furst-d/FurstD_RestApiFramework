<?php

namespace app\http;

use app\Parameter;

/**
 * Represents parameter when using HTTP request method.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class HttpParameter extends Parameter
{
    private ?string $type;
    private mixed $default;

    /**
     * HttpParameter constructor.
     *
     * @param string $name
     * @param bool $editable
     * @param string|null $location
     * @param bool|null $required
     * @param string|null $represents
     * @param ?string $type
     * @param mixed $default
     */
    public function __construct(string $name, bool $editable, ?string $location, ?bool $required, ?string $represents,
                                ?string $type, mixed $default)
    {
        parent::__construct($name, $editable, $location, $required, $represents);
        $this->type = $type;
        $this->default = $default;
    }

    /**
     * Type getter.
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
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