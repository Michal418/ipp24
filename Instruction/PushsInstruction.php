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

class PushsInstruction extends Instruction {
    /**
     * @param Argument $symb
     */
    public function __construct(protected Argument $symb)
    {
        if (!IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException("Invalid argument for PUSHS: {$symb}");
        }

        parent::__construct('PUSHS');
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(InterpreterContext & $context, IO $io) : void {
        $value = $context->getSymbolValue($this->symb);

        if (is_object($value)) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, 'Attempt to read uninitialized value');
        }

        $context->stack[] = $value;
    }

    public function __toString() : string {
        return "{$this->getOpcode()} {$this->symb}";
    }
};

