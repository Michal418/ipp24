<?php

namespace IPP\Student\Instruction;

use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class DefvarInstruction extends Instruction
{
    /**
     * @param Argument $var
     */
    public function __construct(protected Argument $var)
    {
        if ($var->getIppType() != IPPType::VAR) {
            throw new InvalidArgumentException("Argument for DEFVAR must be of type var");
        }

        parent::__construct('DEFVAR');
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $context->defvar($this->var->getText());
    }

    public function __toString(): string
    {
        return "{$this->getOpcode()} {$this->var}";
    }
}

