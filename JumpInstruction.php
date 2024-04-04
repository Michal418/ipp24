<?php

namespace IPP\Student;


use InvalidArgumentException;

class JumpInstruction extends Instruction {
    /**
     * @param Argument $label
     * @throws InvalidArgumentException
     */
    public function __construct(Argument $label)
    {
        if ($label->getIppType() !== IPPType::LABEL) {
            throw new InvalidArgumentException("Invalid argument for JUMP: {$label}");
        }

        parent::__construct('JUMP', [$label]);
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->jump($this->getArguments()[0]->getText());
    }
};

