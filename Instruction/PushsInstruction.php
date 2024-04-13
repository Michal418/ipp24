<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class PushsInstruction extends Instruction
{
    /**
     * @param Argument $symb
     */
    public function __construct(protected Argument $symb)
    {
        if (!IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException("Invalid argument for PUSHS: {$symb}");
        }

        parent::__construct('PUSHS');
    }

    /**
     * @throws InternalErrorException
     * @throws ValueException
     * @throws VariableAccessException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $value = $context->getSymbolValue($this->symb);

        if (!$value->isInitialized()) {
            throw new ValueException('Attempt to read uninitialized value');
        }

        $context->pushStack($value);
    }

    public function __toString(): string
    {
        return "{$this->getOpcode()} {$this->symb}";
    }
}

