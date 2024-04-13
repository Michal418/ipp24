<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\StringOperationException;
use IPP\Student\Exception\TypeException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;
use IPP\Student\Value;

class Int2CharInstruction extends Instruction
{
    /**
     * @param Argument $var
     * @param Argument $symb
     */
    public function __construct(protected Argument $var,
                                protected Argument $symb)
    {
        if ($var->getIppType() !== IPPType::VAR || !IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException("Invalid arguments for INT2CHAR: {$var}, {$symb}");
        }

        parent::__construct('INT2CHAR');
    }

    /**
     * @param InterpreterContext $context
     * @param IO $io
     * @throws InternalErrorException
     * @throws StringOperationException
     * @throws TypeException
     * @throws ValueException
     * @throws FrameAccessException
     * @throws VariableAccessException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $value = $context->getSymbolValue($this->symb);

        if (!$value->isInitialized()) {
            throw new ValueException("Attempt to read uninitialized value");
        }

        $primitiveValue = $value->getValue();

        if (!is_int($primitiveValue)) {
            throw new TypeException("Invalid type for INT2CHAR: $value.");
        }

        if ($primitiveValue < 0 || $primitiveValue > 1_114_111) {
            throw new StringOperationException("Invalid value for INT2CHAR: $value.");
        }

        $result = mb_chr($primitiveValue, encoding: 'UTF-8');
        $context->setVariable($this->var->getText(), new Value(true, $result));
    }

    public function __toString(): string
    {
        return "{$this->getOpcode()} {$this->var} {$this->symb}";
    }
}

