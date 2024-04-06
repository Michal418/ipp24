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

class StrlenInstruction extends Instruction {
    /**
     * @param Argument $var
     * @param Argument $symb
     */
    public function __construct(protected Argument $var,
                                protected Argument $symb)
    {
        if ($var->getIppType() !== IPPType::VAR || !IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException("Invalid arguments for STRLEN: {$var}, {$symb}");
        }

        parent::__construct('STRLEN');
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(InterpreterContext & $context, IO $io) : void {
        $value = $context->getSymbolValue($this->symb);
        if (!is_string($value)) {
            throw new InterpreterRuntimeException(ReturnCode::STRING_OPERATION_ERROR, "Invalid value for STRLEN: $value.");
        }
        $context->setVariable($this->var->getText(), strlen($value));
    }
};

