<?php

namespace app\database;

use app\Content;

/**
 * Represents optional expanded content when using database.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class DatabaseContent extends Content
{
    private ?string $representation;
    private ?Reference $reference;

    /**
     * DatabaseContent constructor.
     *
     * @param string $name
     * @param string $type
     * @param bool $required
     * @param string|null $hash
     * @param string|null $representation
     * @param Reference|null $reference
     */
    public function __construct(string $name, string $type, bool $required, ?string $hash, ?string $representation, ?Reference $reference)
    {
        parent::__construct($name, $type, $required, $hash);
        $this->representation = $representation;
        $this->reference = $reference;
    }

    /**
     * Representation getter.
     *
     * @return string|null
     */
    public function getRepresentation(): ?string
    {
        return $this->representation;
    }

    /**
     * Join getter.
     *
     * @return Reference|null
     */
    public function getReference(): ?Reference
    {
        return $this->reference;
    }
}