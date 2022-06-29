<?php

namespace app;

/**
 * Represents parameter in config.yaml.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class Parameter
{
    private string $name;
    private bool $editable;
    private ?string $location;
    private ?bool $required;
    private ?string $represents;

    /**
     * Parameter constructor.
     *
     * @param string $name
     * @param bool $editable
     * @param string|null $location
     * @param bool|null $required
     * @param string|null $represents
     */
    public function __construct(string $name, bool $editable, ?string $location, ?bool $required, ?string $represents)
    {
        $this->name = $name;
        $this->editable = $editable;
        $this->location = $location;
        $this->required = $required;
        $this->represents = $represents;
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
     * Editable getter.
     *
     * @return bool
     */
    public function isEditable(): bool
    {
        return $this->editable;
    }

    /**
     * Location getter.
     *
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * Required getter.
     *
     * @return bool|null
     */
    public function getRequired(): ?bool
    {
        return $this->required;
    }

    /**
     * Represents getter.
     *
     * @return string|null
     */
    public function getRepresents(): ?string
    {
        return $this->represents;
    }
}