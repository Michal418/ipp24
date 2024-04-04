<?php

namespace IPP\Student;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;

class Int2CharInstruction extends Instruction {
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

        parent::__construct('INT2CHAR', [$var, $symb]);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->int2char($this->getArguments()[0]->getText(), $this->getArguments()[1]);
    }
};

