<?php

namespace IPP\Student\Instruction;


use IPP\Student\InterpreterContext;
use IPP\Student\IO;

class CreateFrameInstruction extends Instruction
{
    public function __construct()
    {
        parent::__construct('CREATEFRAME');
    }

    public function execute(InterpreterContext &$context, IO $io): void
    {
        $context->createFrame();
    }

    public function __toString(): string
    {
        return $this->getOpcode();
    }
}

