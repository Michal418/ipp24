<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Argument;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class DprintInstruction extends Instruction
{
    /**
     * @param Argument $symb
     */
    public function __construct(protected Argument $symb)
    {
        if (!IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException("Invalid argument for DPRINT: {$symb}");
        }

        parent::__construct('DPRINT');
    }

    /**
     * @throws InternalErrorException
     * @throws VariableAccessException
     * @throws ValueException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $value = $context->getSymbolValue($this->symb);
        $io->errString((string)$value);
    }

    public function __toString(): string
    {
        return "{$this->getOpcode()} {$this->symb}";
    }
}
