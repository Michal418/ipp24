<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\Interpreter;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;
use IPP\Student\Uninitialized;

class EqInstruction extends Instruction {
    /**
     * @param Argument $var
     * @param Argument $symb1
     * @param Argument $symb2
     */
    public function __construct(protected Argument $var,
                                protected Argument $symb1,
                                protected Argument $symb2)
    {
        if ($var->getIppType() !== IPPType::VAR || !IPPType::isVarOrData($symb1->getIppType()) || !IPPType::isVarOrData($symb2->getIppType())) {
            throw new InvalidArgumentException("Invalid arguments for EQ: {$var}, {$symb1}, {$symb2}");
        }

        parent::__construct('EQ');
    }

    public function execute(InterpreterContext & $context, IO $io) : void {
        $frame = $context->getFrame($this->var->getText());
        $varName = $context->getVariableName($this->var->getText());
        $context->selectFrame($frame)[$varName] = $context->eq($this->symb1, $this->symb2);
    }

    public function __toString() : string {
        return "{$this->getOpcode()} {$this->var} {$this->symb1} {$this->symb2}";
    }
};

