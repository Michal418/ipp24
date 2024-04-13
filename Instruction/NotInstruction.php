<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Student\Argument;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class NotInstruction extends Instruction
{
    /**
     * @param Argument $var
     * @param Argument $symb
     */
    public function __construct(protected Argument $var,
                                protected Argument $symb)
    {
        if ($var->getIppType() !== IPPType::VAR || !IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException("Invalid arguments for NOT: {$var}, {$symb}");
        }

        parent::__construct('NOT');
    }

    public function execute(InterpreterContext &$context, IO $io): void
    {
        $frame = $context->getFrame($this->var->getText());
        $value = $context->getSymbolValue($this->symb);
        $varName = $context->getVariableName($this->var->getText());
        $result = $value->not();
        $context->selectFrame($frame)->setSymbol($varName, $result);
    }

    public function __toString(): string
    {
        return "{$this->getOpcode()} {$this->var} {$this->symb}";
    }
}

