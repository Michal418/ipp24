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

class SetCharInstruction extends Instruction {
    /**
     * @param Argument $var
     * @param Argument $symb1
     * @param Argument $symb2
     */
    public function __construct(protected Argument $var,
                                protected  Argument $symb1,
                                protected Argument $symb2)
    {
        if ($var->getIppType() !== IPPType::VAR || !IPPType::isVarOrData($symb1->getIppType()) || !IPPType::isVarOrData($symb2->getIppType())) {
            throw new InvalidArgumentException("Invalid arguments for SETCHAR: {$var}, {$symb1}, {$symb2}");
        }

        parent::__construct('SETCHAR');
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(InterpreterContext & $context, IO $io) : void {
        $string = $context->getSymbolValue($this->var);
        $index = $context->getSymbolValue($this->symb1);
        $char = $context->getSymbolValue($this->symb2);
        if (!is_string($string) || !is_int($index) || $index < 0 || $index >= strlen($string) || !is_string($char) || strlen($char) !== 1) {
            throw new InterpreterRuntimeException(ReturnCode::STRING_OPERATION_ERROR, "Invalid value for SETCHAR: $string, $index, $char.");
        }
        $string[$index] = $char;
        $context->setVariable($this->var->getText(), $string);
    }
};

