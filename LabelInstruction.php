<?php

namespace IPP\Student;


use InvalidArgumentException;

class LabelInstruction extends Instruction {
    /**
     * @param Argument $label
     * @throws InvalidArgumentException
     */
    public function __construct(Argument $label)
    {
        if ($label->getIppType() !== IPPType::LABEL) {
            throw new InvalidArgumentException();
        }

        parent::__construct('LABEL', [$label]);
    }

    public function execute(Interpreter $interpreter) : void {
        $interpreter->label($this->getArguments()[0]->getText());
    }
};

