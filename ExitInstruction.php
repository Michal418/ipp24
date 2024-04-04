<?php

namespace IPP\Student;

use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;

class ExitInstruction extends Instruction {
    /**
     * @param Argument $symb
     * @throws InvalidArgumentException
     */
    public function __construct(Argument $symb)
    {
        if (!IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException();
        }

        parent::__construct('EXIT', [$symb]);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->exit($this->getArguments()[0]);
    }
};

