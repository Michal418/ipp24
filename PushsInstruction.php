<?php

namespace IPP\Student;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;

class PushsInstruction extends Instruction {
    /**
     * @param Argument $symb
     * @throws InvalidArgumentException
     */
    public function __construct(Argument $symb)
    {
        if (!IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException("Invalid argument for PUSHS: {$symb}");
        }

        parent::__construct('PUSHS', [$symb]);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->pushs($this->getArguments()[0]);
    }
};

