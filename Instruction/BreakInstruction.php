<?php

namespace IPP\Student\Instruction;

use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\Uninitialized;

class BreakInstruction extends Instruction {
    public function __construct()
    {
        parent::__construct('BREAK');
    }

    public function execute(InterpreterContext & $context, IO $io) : void {
        $io->errString('Program counter: ' . $context->programCounter . PHP_EOL);
        $io->errString('Global frame: ' . print_r($context->globalFrame, true) . PHP_EOL);
        $io->errString('Local frame: ' . print_r((!empty($this->frameStack)) ? $this->frameStack[0] : 'empty', true) . PHP_EOL);
        $io->errString('Temporary frame: ' . print_r($context->tempFrame, true) . PHP_EOL);
    }
}
