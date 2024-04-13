<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class CallInstruction extends Instruction
{
    /**
     * @param Argument $label
     */
    public function __construct(protected Argument $label)
    {
        if ($label->getIppType() !== IPPType::LABEL) {
            throw new InvalidArgumentException("Invalid argument for CALL: {$label}");
        }

        parent::__construct('CALL');
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InterpreterRuntimeException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $pc = $context->findLabel($this->label->getText());
        $context->pushCallStack($context->getProgramCounter());
        $context->setProgramCounter($pc);
    }

    public function __toString(): string
    {
        return "{$this->getOpcode()} {$this->label}";
    }
}

