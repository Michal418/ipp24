<?php

namespace IPP\Student\Instruction;


use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;

class PopFrameInstruction extends Instruction
{
    public function __construct()
    {
        parent::__construct('POPFRAME');
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $context->popFrame();
    }

    public function __toString(): string
    {
        return $this->getOpcode();
    }
}

