<?php

namespace IPP\Student\Instruction;

use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Argument;
use IPP\Student\Exception\OperandValueException;
use IPP\Student\Exception\TypeException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class ExitInstruction extends Instruction
{
    /**
     * @param Argument $symb
     */
    public function __construct(protected Argument $symb)
    {
        if (!IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException("Invalid argument for EXIT: {$symb}");
        }

        parent::__construct('EXIT');
    }

    /**
     * @throws InternalErrorException
     * @throws VariableAccessException
     * @throws ValueException
     * @throws OperandValueException
     * @throws TypeException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $exitCode = $context->getSymbolValue($this->symb);

        if (!$exitCode->isInitialized()) {
            throw new ValueException("Attempt to read uninitialized value");
        }

        $value = $exitCode->getValue();
        if (!is_int($value)) {
            $exitType = gettype($value);
            throw new TypeException("Invalid type for EXIT: ($exitType) '$exitCode'.");
        }

        if ($value < 0 || $value > 9) {
            throw new OperandValueException("Invalid value for EXIT: $exitCode.");
        }

        $context->setExitCode($value);
        $context->stop();
    }

    public function __toString(): string
    {
        return "{$this->getOpcode()} {$this->symb}";
    }
}

