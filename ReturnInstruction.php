<?php

namespace IPP\Student;

class ReturnInstruction extends Instruction {
    public function __construct()
    {
        parent::__construct('RETURN', []);
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->return_instruction();
    }
};


