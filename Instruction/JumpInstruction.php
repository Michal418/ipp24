<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Student\Argument;
use IPP\Student\Exception\SemanticErrorException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class JumpInstruction extends Instruction
{
    /**
     * @param Argument $label
     */
    public function __construct(protected Argument $label)
    {
        if ($label->getIppType() !== IPPType::LABEL) {
            throw new InvalidArgumentException("Invalid argument for JUMP: {$label}");
        }

        parent::__construct('JUMP');
    }

    /**
     * @param InterpreterContext $context
     * @param IO $io
     * @throws SemanticErrorException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $label = $this->label->getText();
        $pc = $context->findLabel($label);
        $context->setProgramCounter($pc);
    }

    public function __toString(): string
    {
        return "{$this->getOpcode()} {$this->label}";
    }
}

