<?php

namespace IPP\Student;


class PopFrameInstruction extends Instruction {
    public function __construct()
    {
        parent::__construct('POPFRAME', []);
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->popFrame();
    }
};

