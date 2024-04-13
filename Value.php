<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\Exception\InterpreterRuntimeException;

class Value
{
    private int|string|bool|null $data;
    private bool $initialized;

    public function __construct(bool $initialize = false, int|string|bool|null $value = null) {
        $this->initialized = $initialize;
        $this->data = $value;
    }

    public function __toString() : string {
        if (!$this->isInitialized()) {
            return "{uninitialized}";
        }

        if (is_int($this->data)) {
            $str = (int) $this->data;
        }
        elseif (is_bool($this->data)) {
            $str = $this->data ? 'true' : 'false';
        }
        elseif (is_null($this->data)) {
            $str = 'nil';
        }
        else {
            $str = (string) $this->data;
        }

        $type = gettype($this->data);
        return "{($type) $str}";
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function getValue() : int|string|bool|null {
        if (!$this->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, 'Attempt to read uninitialized value');
        }

        return $this->data;
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function getString() : string {
        $result = $this->data;

        if (!$this->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR,
                'Attempt to read uninitialized value.');
        }

        if (!is_string($result)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "$this is not string.");
        }

        return $result;
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function getInt() : int {
        $result = $this->data;

        if (!$this->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR,
                'Attempt to read uninitialized value.');
        }

        if (!is_int($result)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "$this is not int.");
        }

        return $result;
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function getBool() : bool {
        $result = $this->data;

        if (!$this->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR,
                'Attempt to read uninitialized value.');
        }

        if (!is_bool($result)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "$this is not int.");
        }

        return $result;
    }

    public function isNil() : bool {
        return $this->initialized && is_null($this->data);
    }

    public function setValue(int|string|bool|null $value): void {
        $this->data = $value;
        $this->initialized = true;
    }

    public function isInitialized() : bool {
        return $this->initialized;
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function add(Value $other) : Value {
        if (!$this->isInitialized() || !$other->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR,
                "Attempt to read uninitialized value");
        }

        if (!is_int($this->data) || !is_int($other->data)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR,
                "Incompatible types for addition: $this, $other.");

        }

        return new Value(true, $this->data + $other->data);
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function and(Value $other) : Value {
        if (!$this->isInitialized() || !$other->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR,
                "Attempt to read uninitialized value");
        }

        if (!is_bool($this->data) || !is_bool($other->data)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR,
                "Incompatible types for AND: $this, $other.");
        }

        return new Value(true, $this->data && $other->data);
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function concat(Value $other) : Value {
        if (!$this->isInitialized() || !$other->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR,
                "Attempt to read uninitialized value");
        }

        $value1 = $this->getValue();
        $value2 = $other->getValue();

        if (!is_string($value1) || !is_string($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR,
                "Incompatible types for CONCAT: $this, $other.");
        }

        return new Value(true, $value1 . $value2);
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function gt(Value $other) : Value {
        if (!$this->isInitialized() || !$other->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR,
                "Attempt to read uninitialized value");
        }

        $value1 = $this->getValue();
        $value2 = $other->getValue();

        if (is_string($value1) && is_string($value2)) {
            $result = strcmp($value1, $value2) > 0;
        }
        elseif (is_int($value1) && is_int($value2)) {
            $result = $value1 > $value2;
        }
        elseif (is_bool($value1) && is_bool($value2)) {
            $result = $value1 === true && $value2 === false;
        }
        else {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR,
                "Incompatible types for comparison: $this, $other.");
        }

        return new Value(true, $result);
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function div(Value $other) : Value {
        if (!$this->isInitialized() || !$other->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR,
                "Attempt to read uninitialized value");
        }

        $value1 = $this->getValue();
        $value2 = $other->getValue();

        if (!is_int($value1) || !is_int($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR,
                "Incompatible types for division: $value1, $value2.");
        }

        if ($value2 === 0) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_VALUE_ERROR, "Incompatible value for division: $value1, $value2.");
        }

        return new Value(true, $value1 / $value2);
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function eq(Value $other) : Value {
        if (!$this->isInitialized() || !$other->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Attempt to read uninitialized value");
        }

        $value1 = $this->getValue();
        $value2 = $other->getValue();

        if  (gettype($value1) !== gettype($value2) && !is_null($value1) && !is_null($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR,
                "Incompatible types for comparison: $this, $other.");
        }

        return new Value(true, $value1 === $value2);
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function lt(Value $other) : Value {
        if (!$this->isInitialized() || !$other->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR,
                "Attempt to read uninitialized value");
        }

        $value1 = $this->getValue();
        $value2 = $other->getValue();

        if (is_string($value1) && is_string($value2)) {
            $result = strcmp($value1, $value2) < 0;
        }
        elseif (is_int($value1) && is_int($value2)) {
            $result = $value1 < $value2;
        }
        elseif (is_bool($value1) && is_bool($value2)) {
            $result = $value1 === false && $value2 === true;
        }
        else {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR,
                "Incompatible types for comparison: $this, $other.");
        }

        return new Value(true, $result);
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function not() : Value {
        if (!$this->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR,
                'Attempt to read uninitialized value');
        }

        $value = $this->getValue();

        if (!is_bool($value)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR,
                "Incompatible type for logical NOT: $this.");
        }

        return new Value(true, !$value);
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function or(Value $other) : Value {
        if (!$this->isInitialized() || !$other->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR,
                'Attempt to read uninitialized value');
        }

        $value1 = $this->getValue();
        $value2 = $other->getValue();

        if (!is_bool($value1) || !is_bool($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR,
                "Incompatible types for logical OR: $this, $other.");
        }

        return new Value(true, $value1 || $value2);
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function sub(Value $other) : Value {
        if (!$this->isInitialized() || !$other->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, 'Attempt to read uninitialized value');
        }

        $value1 = $this->getValue();
        $value2 = $other->getValue();

        if (!is_int($value1) || !is_int($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR,
            "Incompatible types for subtraction: $this, $$other.");
        }

        return new Value(true, $value1 - $value2);
    }
}