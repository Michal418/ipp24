<?php

namespace IPP\Student\Instruction;

use IPP\Student\InterpreterContext;
use IPP\Student\IO;

class BreakInstruction extends Instruction
{
    public function __construct()
    {
        parent::__construct('BREAK');
    }

    public function execute(InterpreterContext &$context, IO $io): void
    {
        $io->errString('Program counter: ' . $context->getProgramCounter() . PHP_EOL);
        $io->errString('Global frame: ' . print_r($context->getGlobalFrame(), true) . PHP_EOL);
        $io->errString('Local frame: ' .
            print_r((!empty($this->frameStack)) ? $this->frameStack[0] : 'empty', true) . PHP_EOL);
        $io->errString('Temporary frame: ' . print_r($context->getTemporaryFrame(), true) . PHP_EOL);
    }

    public function __toString(): string
    {
        return $this->getOpcode();
    }
}
