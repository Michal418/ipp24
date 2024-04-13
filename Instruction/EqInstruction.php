<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Argument;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class EqInstruction extends Instruction
{
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

    /**
     * @throws InternalErrorException
     * @throws ValueException
     * @throws VariableAccessException
     * @throws FrameAccessException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $frame = $context->getFrame($this->var->getText());
        $varName = $context->getVariableName($this->var->getText());
        $value1 = $context->getSymbolValue($this->symb1);
        $value2 = $context->getSymbolValue($this->symb2);
        $context->selectFrame($frame)->setSymbol($varName, $value1->eq($value2));
    }

    public function __toString(): string
    {
        return "{$this->getOpcode()} {$this->var} {$this->symb1} {$this->symb2}";
    }
}

