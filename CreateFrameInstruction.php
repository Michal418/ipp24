<?php

namespace IPP\Student;


class CreateFrameInstruction extends Instruction {
    public function __construct()
    {
        parent::__construct('CREATEFRAME', []);
    }

    public function execute(Interpreter $interpreter) : void {
        $interpreter->createFrame();
    }
};

