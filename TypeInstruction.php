<?php

namespace IPP\Student;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;

class TypeInstruction extends Instruction {
    /**
     * @param Argument $var
     * @param Argument $symb
     * @throws InvalidArgumentException
     */
    public function __construct(Argument $var, Argument $symb)
    {
        if ($var->getIppType() !== IPPType::VAR || !IPPType::isDataType($symb->getIppType())) {
            throw new InvalidArgumentException();
        }

        parent::__construct('TYPE', [$var, $symb]);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->type($this->getArguments()[0]->getText(), $this->getArguments()[1]);
    }
};

