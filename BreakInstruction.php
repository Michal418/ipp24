<?php

namespace IPP\Student;


class BreakInstruction extends Instruction {
    public function __construct()
    {
        parent::__construct('BREAK', []);
    }

    public function execute(Interpreter $interpreter) : void {
        $interpreter->break_instruction();
    }
}
