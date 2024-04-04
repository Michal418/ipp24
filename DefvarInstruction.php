<?php

namespace IPP\Student;

use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;

class DefvarInstruction extends Instruction {
    /**
     * @param Argument $var
     * @throws InvalidArgumentException
     */
    public function __construct(Argument $var)
    {
        if ($var->getIppType() != IPPType::VAR) {
            throw new InvalidArgumentException("Argument for DEFVAR must be of type var");
        }

        parent::__construct('DEFVAR', [$var]);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->defvar($this->getArguments()[0]->getText());
    }
};

