<?php

namespace IPP\Student;

use IPP\Student\Exception\ValueException;

class Frame
{
    /**
     * @var array<string, Value> $data
     */
    private array $data = [];

    /**
     * @throws ValueException
     */
    public function defineSymbol(string $name): void
    {
        if (array_key_exists($name, $this->data)) {
            throw new ValueException("Double declaration of '$$name'.");
        }

        $this->data[$name] = new Value(false);
    }

    /**
     * @throws ValueException
     */
    public function setSymbol(string $name, Value $value): void
    {
        if (!array_key_exists($name, $this->data)) {
            throw new ValueException("Attempt to read undefined symbol '$name'.");
        }

        $this->data[$name] = $value;
    }

    /**
     * @throws ValueException
     */
    public function getSymbolValue(string $name): Value
    {
        if (!array_key_exists($name, $this->data)) {
            throw new ValueException("Attempt to read undefined symbol '$name'.");
        }

        return $this->data[$name];
    }

    public function isSymbolDefined(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }
}