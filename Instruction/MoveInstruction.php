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

class MoveInstruction extends Instruction
{
    /**
     * @param Argument $var
     * @param Argument $symb
     */
    public function __construct(protected Argument $var,
                                protected Argument $symb)
    {
        if ($var->getIppType() !== IPPType::VAR || !IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException("Invalid arguments for MOVE: {$var}, {$symb}");
        }

        parent::__construct('MOVE');
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $value = $context->getSymbolValue($this->symb);

        if (!$value->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR,
                'Attempt to read uninitialized value');
        }

        $context->setVariable($this->var->getText(), $value);
    }

    public function __toString(): string
    {
        return "{$this->getOpcode()} {$this->var} {$this->symb}";
    }
}


