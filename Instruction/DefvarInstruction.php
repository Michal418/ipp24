<?php

namespace IPP\Student\Instruction;

use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Argument;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\SemanticErrorException;
use IPP\Student\Exception\ValueException;
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
     * @throws InternalErrorException
     * @throws FrameAccessException
     * @throws ValueException
     * @throws SemanticErrorException
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

