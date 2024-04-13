<?php

namespace IPP\Student\Instruction;

use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;

/**
 * @brief Třída reprezentující instrukci.
 */
abstract class Instruction
{
    /**
     * @param string $opcode
     */
    public function __construct(private readonly string $opcode)
    {
    }

    public function getOpcode(): string
    {
        return $this->opcode;
    }

    /**
     * @param InterpreterContext $context
     * @param IO $io
     * @return void
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    abstract public function execute(InterpreterContext &$context, IO $io): void;

    abstract public function __toString(): string;
}


