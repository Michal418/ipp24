<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Student\Argument;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;
use RuntimeException;

class LabelInstruction extends Instruction {
    /**
     * @param Argument $label
     */
    public function __construct(protected Argument $label)
    {
        if ($label->getIppType() !== IPPType::LABEL) {
            throw new InvalidArgumentException("Invalid argument for LABEL: {$label}");
        }

        parent::__construct('LABEL');
    }

    public function execute(InterpreterContext & $context, IO $io) : void {
    }

    public function getLabel() : string {
        return $this->label->getText();
    }

    public function __toString() : string {
        return "{$this->getOpcode()} {$this->label}";
    }
};

