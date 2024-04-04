<?php

namespace IPP\Student;


use InvalidArgumentException;

class ConcatInstruction extends Instruction {
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

        parent::__construct('CONCAT', [$var, $symb1, $symb2]);
    }

    public function execute(Interpreter $interpreter) : void {
        $interpreter->concat($this->getArguments()[0]->getText(), $this->getArguments()[1], $this->getArguments()[2]);
    }
};