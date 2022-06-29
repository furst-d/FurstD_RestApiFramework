<?php

namespace app\controller;

/**
 * Class Abstract.
 * Represents an abstract controller that provides the methods needed to call a callback.
 *
 * @author Dominik Fűrst <st60987@upce.cz>
 */
class AbstractController
{
    private string $class;
    private string $action;

    /**
     * Class getter.
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Action getter.
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Class setter.
     *
     * @param string $class
     */
    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    /**
     * Action setter.
     *
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }
}