<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class JumpInstruction extends Instruction {
    /**
     * @param Argument $label
     */
    public function __construct(protected Argument $label)
    {
        if ($label->getIppType() !== IPPType::LABEL) {
            throw new InvalidArgumentException("Invalid argument for JUMP: {$label}");
        }

        parent::__construct('JUMP');
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function execute(InterpreterContext & $context, IO $io) : void {
        $context->programCounter = $context->findLabel($this->label->getText());
    }
};

