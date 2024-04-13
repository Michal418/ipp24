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

class WriteInstruction extends Instruction
{
    /**
     * @param Argument $symb
     */
    public function __construct(protected Argument $symb)
    {
        if (!IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException("Invalid argument for WRITE: {$symb}");
        }

        parent::__construct('WRITE');
    }

    /**
     * @param InterpreterContext $context
     * @param IO $io
     * @throws InternalErrorException
     * @throws ValueException
     * @throws VariableAccessException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $symb = $context->getSymbolValue($this->symb);

        if (!$symb->isInitialized()) {
            throw new ValueException("Uninitialized variable used in WRITE: $symb");
        }

        $value = $symb->getValue();

        if (is_string($value)) {
            $io->writeString($value);
        } elseif (is_int($value)) {
            $io->writeInt($value);
        } elseif (is_bool($value)) {
            $io->writeBool($value);
        } else {
            $io->writeString('');
        }
    }

    public function __toString(): string
    {
        return "{$this->getOpcode()} {$this->symb}";
    }
}

