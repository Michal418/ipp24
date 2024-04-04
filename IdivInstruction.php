<?php

namespace IPP\Student;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;

class IdivInstruction extends Instruction {
    /**
     * @param Argument $var
     * @param Argument $symb1
     * @param Argument $symb2
     * @throws InvalidArgumentException
     */
    public function __construct(Argument $var, Argument $symb1, Argument $symb2)
    {
        if ($var->getIppType() !== IPPType::VAR || !IPPType::isVarOrData($symb1->getIppType()) || !IPPType::isVarOrData($symb2->getIppType())) {
            throw new InvalidArgumentException();
        }

        parent::__construct('IDIV', [$var, $symb1, $symb2]);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->idiv($this->getArguments()[0]->getText(), $this->getArguments()[1], $this->getArguments()[2]);
    }
};

