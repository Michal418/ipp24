<?php

namespace IPP\Student\Instruction;


use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\Interpreter;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\Uninitialized;

class PushFrameInstruction extends Instruction {
    public function __construct()
    {
        parent::__construct('PUSHFRAME');
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function execute(InterpreterContext & $context, IO $io) : void {
        $context->pushFrame();
    }

    public function __toString() : string {
        return $this->getOpcode();
    }
};

