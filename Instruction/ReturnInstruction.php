<?php

namespace IPP\Student\Instruction;

use IPP\Core\ReturnCode;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;

class ReturnInstruction extends Instruction {
    public function __construct()
    {
        parent::__construct('RETURN');
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function execute(InterpreterContext & $context, IO $io) : void {
        $result = array_pop($context->callStack);

        if (is_null($result)) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Call stack is empty.");
        }

        $context->programCounter = $result;
    }
};


