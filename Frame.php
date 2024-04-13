<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\Exception\InterpreterRuntimeException;

class Frame
{
    /**
     * @var array<string, Value> $data
     */
    private array $data = [];

    /**
     * @throws InterpreterRuntimeException
     */
    public function defineSymbol(string $name): void
    {
        if (array_key_exists($name, $this->data)) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Double declaration of '$$name'.");
        }

        $this->data[$name] = new Value(false);
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function setSymbol(string $name, Value $value): void
    {
        if (!array_key_exists($name, $this->data)) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Attempt to read undefined symbol '$name'.");
        }

        $this->data[$name] = $value;
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function getSymbolValue(string $name): Value
    {
        if (!array_key_exists($name, $this->data)) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Attempt to read undefined symbol '$name'.");
        }

        return $this->data[$name];
    }

    public function isSymbolDefined(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }
}