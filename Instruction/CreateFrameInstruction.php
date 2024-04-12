<?php

namespace IPP\Student\Instruction;


use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Student\Interpreter;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\Uninitialized;

class CreateFrameInstruction extends Instruction {
    public function __construct()
    {
        parent::__construct('CREATEFRAME');
    }

    public function execute(InterpreterContext & $context, IO $io) : void {
        $context->createFrame();
    }

    public function __toString() : string {
        return $this->getOpcode();
    }
};

