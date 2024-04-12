<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\Interpreter;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;
use IPP\Student\Uninitialized;

class ConcatInstruction extends Instruction {
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
            throw new InvalidArgumentException("Invalid arguments for CONCAT: {$var}, {$symb1}, {$symb2}");
        }

        parent::__construct('CONCAT');
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

        if (!is_string($value1) || !is_string($value2)) {
            $type1 = gettype($value1);
            $type2 = gettype($value2);
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Invalid value for CONCAT: ($type1) '$value1', ($type2) '$value2'.");
        }

        $context->setVariable($this->var->getText(), $value1 . $value2);
    }

    public function __toString() : string {
        return "}{$this->getOpcode()} {$this->var} {$this->symb1} {$this->symb2}";
    }
};


