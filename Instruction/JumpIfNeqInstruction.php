<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Argument;
use IPP\Student\Exception\SemanticErrorException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class JumpIfNeqInstruction extends Instruction
{
    /**
     * @param Argument $label
     * @param Argument $symb1
     * @param Argument $symb2
     */
    public function __construct(protected Argument $label,
                                protected Argument $symb1,
                                protected Argument $symb2)
    {
        if ($label->getIppType() !== IPPType::LABEL || !IPPType::isVarOrData($symb1->getIppType()) || !IPPType::isVarOrData($symb2->getIppType())) {
            throw new InvalidArgumentException("Invalid arguments for JUMPIFNEQ: {$label}, {$symb1}, {$symb2}");
        }

        parent::__construct('JUMPIFNEQ');
    }

    /**
     * @param InterpreterContext $context
     * @param IO $io
     * @throws InternalErrorException
     * @throws SemanticErrorException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        if (!$context->eq($this->symb1, $this->symb2)) {
            $label = $this->label->getText();
            $pc = $context->findLabel($label);
            $context->setProgramCounter($pc);
        }
    }

    public function __toString(): string
    {
        return "{$this->getOpcode()} {$this->label} {$this->symb1} {$this->symb2}";
    }
}

