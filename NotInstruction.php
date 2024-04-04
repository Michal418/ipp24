<?php

namespace IPP\Student;


use InvalidArgumentException;

class NotInstruction extends Instruction {
    /**
     * @param Argument $var
     * @param Argument $symb
     * @throws InvalidArgumentException
     */
    public function __construct(Argument $var, Argument $symb)
    {
        if ($var->getIppType() !== IPPType::VAR || !IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException();
        }

        parent::__construct('NOT', [$var, $symb]);
    }

    public function execute(Interpreter $interpreter) : void {
        $interpreter->not($this->getArguments()[0]->getText(), $this->getArguments()[1]);
    }
};

