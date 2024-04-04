<?php

namespace IPP\Student;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;

class WriteInstruction extends Instruction {
    /**
     * @param Argument $symb
     * @throws InvalidArgumentException
     */
    public function __construct(Argument $symb)
    {
        if (!IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException("Invalid argument for WRITE: {$symb}");
        }

        parent::__construct('WRITE', [$symb]);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->write($this->getArguments()[0]);
    }
}

