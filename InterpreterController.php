<?php

namespace IPP\Student;

use Closure;

class InterpreterController
{
    public function __construct(
        public Closure $selectFrame,
        public Closure $getSymbolValue,
        public Closure $setVariable,
        public Closure $defVar,
        public Closure $pushFrame,
        public Closure $createFrame,
        public Closure $popFrame)
    {
    }
}