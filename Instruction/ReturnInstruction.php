<?php

namespace IPP\Student\Instruction;

use IPP\Student\Exception\ValueException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;

class ReturnInstruction extends Instruction
{
    public function __construct()
    {
        parent::__construct('RETURN');
    }

    /**
     * @throws ValueException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $result = $context->popCallStack();
        $context->setProgramCounter($result);
    }

    public function __toString(): string
    {
        return $this->getOpcode();
    }
}


