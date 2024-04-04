<?php

namespace IPP\Student;

use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;

class PopsInstruction extends Instruction
{
    /**
     * @param Argument $var
     * @throws InvalidArgumentException
     */
    public function __construct(Argument $var)
    {
        if ($var->getIppType() !== IPPType::VAR) {
            throw new InvalidArgumentException("Invalid argument for POPS: {$var}");
        }

        parent::__construct('PUSHS', [$var]);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->pops($this->arguments[0]->getText());
    }
}
