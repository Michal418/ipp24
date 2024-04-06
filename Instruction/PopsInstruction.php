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

class PopsInstruction extends Instruction
{
    /**
     * @param Argument $var
     */
    public function __construct(protected Argument $var)
    {
        if ($var->getIppType() !== IPPType::VAR) {
            throw new InvalidArgumentException("Invalid argument for POPS: {$var}");
        }

        parent::__construct('PUSHS');
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(InterpreterContext & $context, IO $io) : void {
        $result = array_pop($context->stack);
        if (is_null($result)) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Stack is empty.");
        }
        $context->setVariable($this->var->getText(), $result);
    }
}
