<?php

namespace IPP\Student;


use InvalidArgumentException;

class CallInstruction extends Instruction {
    /**
     * @param Argument $label
     * @throws InvalidArgumentException
     */
    public function __construct(Argument $label)
    {
        if ($label->getIppType() !== IPPType::LABEL) {
            throw new InvalidArgumentException("Invalid argument for CALL: {$label}");
        }

        parent::__construct('CALL', [$label]);
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->call($this->getArguments()[0]->getText());
    }
};

