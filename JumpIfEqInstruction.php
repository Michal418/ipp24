<?php

namespace IPP\Student;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;

class JumpIfEqInstruction extends Instruction {
    /**
     * @param Argument $label
     * @param Argument $symb1
     * @param Argument $symb2
     * @throws InvalidArgumentException
     */
    public function __construct(Argument $label, Argument $symb1, Argument $symb2)
    {
        if ($label->getIppType() !== IPPType::LABEL || !IPPType::isVarOrData($symb1->getIppType()) || !IPPType::isVarOrData($symb2->getIppType())) {
            throw new InvalidArgumentException("Invlaid arguments for JUMPIFEQ: {$label}, {$symb1}, {$symb2}");
        }

        parent::__construct('JUMPIFEQ', [$label, $symb1, $symb2]);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->jumpifeq($this->getArguments()[0]->getText(), $this->getArguments()[1], $this->getArguments()[2]);
    }
};

