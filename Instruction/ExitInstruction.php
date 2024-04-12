<?php

namespace IPP\Student\Instruction;

use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class ExitInstruction extends Instruction {
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
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(InterpreterContext & $context, IO $io) : void {
        $exitCode = $context->getSymbolValue($this->symb);

        if (is_object($exitCode)) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Attempt to read uninitialized value");
        }

        if (!is_int($exitCode)) {
            $exitType = gettype($exitCode);
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type for EXIT: ($exitType) '$exitCode'.");
        }

        if ($exitCode < 0 || $exitCode > 9) {
            $exitType = gettype($exitCode);
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_VALUE_ERROR, "Invalid value for EXIT: ($exitType) '$exitCode'.");
        }

        $context->exitCode = $exitCode;
        $context->running = false;
    }

    public function __toString() : string {
        return "{$this->getOpcode()} {$this->symb}";
    }
};

