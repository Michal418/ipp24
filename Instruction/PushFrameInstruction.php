<?php

namespace IPP\Student\Instruction;


use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;

class PushFrameInstruction extends Instruction
{
    public function __construct()
    {
        parent::__construct('PUSHFRAME');
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $context->pushFrame();
    }

    public function __toString(): string
    {
        return $this->getOpcode();
    }
}

