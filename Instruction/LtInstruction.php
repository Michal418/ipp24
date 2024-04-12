<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class LtInstruction extends Instruction {
    /**
     * @param Argument $var
     * @param Argument $symb1
     * @param Argument $symb2
     */
    public function __construct(protected Argument $var,
                                protected Argument $symb1,
                                protected Argument $symb2)
    {
        if ($var->getIppType() !== IPPType::VAR || !IPPType::isVarOrData($symb1->getIppType()) || !IPPType::isVarOrData($symb2->getIppType())) {
            throw new InvalidArgumentException("Invalid arguments for LT: {$var}, {$symb1}, {$symb2}");
        }

        parent::__construct('LT');
    }

    public function execute(InterpreterContext & $context, IO $io) : void {
        $frame = $context->getFrame($this->var->getText());
        $value1 = $context->getSymbolValue($this->symb1);
        $value2 = $context->getSymbolValue($this->symb2);
        $varName = $context->getVariableName($this->var->getText());

        if (is_object($value1) || is_object($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Attempt to read uninitialized value");
        }
        elseif (is_string($value1) && is_string($value2)) {
            $result = strcmp($value1, $value2) < 0;
        }
        elseif (is_int($value1) && is_int($value2)) {
            $result = $value1 < $value2;
        }
        elseif (is_bool($value1) && is_bool($value2)) {
            $result = $value1 === false && $value2 === true;
        }
        else {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Incompatible types for comparison: $value1, $value2.");
        }

        $context->selectFrame($frame)[$varName] = $result;
    }

    public function __toString() : string {
        return "{$this->getOpcode()} {$this->var} {$this->symb1} {$this->symb2}";
    }
};

