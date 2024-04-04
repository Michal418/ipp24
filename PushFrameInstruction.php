<?php

namespace IPP\Student;


class PushFrameInstruction extends Instruction {
    public function __construct()
    {
        parent::__construct('PUSHFRAME', []);
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->pushFrame();
    }
};

