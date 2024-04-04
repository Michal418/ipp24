<?php

namespace IPP\Student;

use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;

class SetCharInstruction extends Instruction {
    /**
     * @param Argument $var
     * @param Argument $symb1
     * @param Argument $symb2
     * @throws InvalidArgumentException
     */
    public function __construct(Argument $var, Argument $symb1, Argument $symb2)
    {
        if ($var->getIppType() !== IPPType::VAR || !IPPType::isVarOrData($symb1->getIppType()) || !IPPType::isVarOrData($symb2->getIppType())) {
            throw new InvalidArgumentException("Invlaid arguments for SETCHAR: {$var}, {$symb1}, {$symb2}");
        }

        parent::__construct('SETCHAR', [$var, $symb1, $symb2]);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->setchar($this->getArguments()[0]->getText(), $this->getArguments()[1], $this->getArguments()[2], $this->getArguments()[3]);
    }
};

