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

class WriteInstruction extends Instruction {
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
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(InterpreterContext & $context, IO $io) : void {
        $value = $context->getSymbolValue($this->symb);
        if (is_string($value)) {
            $io->writeString($value);
        }
        elseif (is_int($value)) {
            $io->writeInt($value);
        }
        elseif (is_bool($value)) {
            $io->writeBool($value);
        }
        elseif (is_null($value)) {
            $io->writeString('');
        }
        else {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Uninitialized variable used in WRITE: $value");
        }
    }

    public function __toString() : string {
        return "{$this->getOpcode()} {$this->symb}";
    }
}

