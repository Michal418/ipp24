<?php

namespace IPP\Student\Instruction;

use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\Exception\TypeException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class AddInstruction extends Instruction
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
            throw new InvalidArgumentException("Invalid arguments for ADD: {$var}, {$symb1}, {$symb2}");
        }

        parent::__construct('ADD');
    }

    /**
     * @throws InternalErrorException
     * @throws ValueException
     * @throws VariableAccessException
     * @throws FrameAccessException
     * @throws TypeException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $value1 = $context->getSymbolValue($this->symb1);
        $value2 = $context->getSymbolValue($this->symb2);

        if (!$value1->isInitialized() || !$value2->isInitialized()) {
            throw new ValueException("Attempt to read uninitialized value");
        }

        if (!is_int($value1->getValue()) || !is_int($value2->getValue())) {
            throw new TypeException("Incompatible types for addition: $value1, $value2.");
        }

        $context->setVariable($this->var->getText(), $value1->add($value2));
    }

    public function __toString(): string
    {
        return "{$this->getOpcode()} {$this->var} {$this->symb1} {$this->symb2}";
    }
}
