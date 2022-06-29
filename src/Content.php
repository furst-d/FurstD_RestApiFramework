<?php

namespace app;

/**
 * Represents body content in request.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class Content
{
    private string $name;
    private string $type;
    private bool $required;
    private ?string $hash;

    /**
     * Content constructor.
     *
     * @param string $name
     * @param string $type
     * @param bool $required
     * @param string|null $hash
     */
    public function __construct(string $name, string $type, bool $required, ?string $hash)
    {
        $this->name = $name;
        $this->type = $type;
        $this->required = $required;
        $this->hash = $hash;
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
     * Type getter.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Required getter.
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return string|null
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }
}