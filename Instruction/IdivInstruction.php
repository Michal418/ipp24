<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class IdivInstruction extends Instruction {
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
            throw new InvalidArgumentException("Invalid arguments for IDIV: {$var}, {$symb1}, {$symb2}");
        }

        parent::__construct('IDIV');
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(InterpreterContext & $context, IO $io) : void {
        $value1 = $context->getSymbolValue($this->symb1);
        $value2 = $context->getSymbolValue($this->symb2);

        if (is_object($value1) || is_object($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Attempt to read uninitialized value");
        }

        if (!is_int($value1) || !is_int($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Incompatible types for division: $value1, $value2.");
        }

        if ($value2 === 0) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_VALUE_ERROR, "Incompatible value for division: $value1, $value2.");
        }

        $context->setVariable($this->var->getText(), $value1 / $value2);
    }

    public function __toString() : string {
        return "{$this->getOpcode()} {$this->var} {$this->symb1} {$this->symb2}";
    }
};


