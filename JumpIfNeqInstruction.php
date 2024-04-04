<?php

namespace IPP\Student;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;

class JumpIfNeqInstruction extends Instruction {
    /**
     * @param Argument $label
     * @param Argument $symb1
     * @param Argument $symb2
     * @throws InvalidArgumentException
     */
    public function __construct(Argument $label, Argument $symb1, Argument $symb2)
    {
        if ($label->getIppType() !== IPPType::LABEL || !IPPType::isDataType($symb1->getIppType()) || !IPPType::isDataType($symb2->getIppType())) {
            throw new InvalidArgumentException();
        }

        parent::__construct('JUMPIFNEQ', [$label, $symb1, $symb2]);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->jumpifneq($this->getArguments()[0]->getText(), $this->getArguments()[1], $this->getArguments()[2]);
    }
};

