<?php

namespace IPP\Student;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;

class DprintInstruction extends Instruction {
    /**
     * @param Argument $symb
     * @throws InvalidArgumentException
     */
    public function __construct(Argument $symb)
    {
        if (!IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException();
        }

        parent::__construct('DPRINT', [$symb]);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->dprint($this->getArguments()[0]);
    }
};
