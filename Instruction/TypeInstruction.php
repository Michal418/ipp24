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
use IPP\Student\Value;

class TypeInstruction extends Instruction
{
    /**
     * @param Argument $var
     * @param Argument $symb
     */
    public function __construct(protected Argument $var,
                                protected Argument $symb)
    {
        if ($var->getIppType() !== IPPType::VAR || !IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException("Invalid arguments for TYPE: {$var}, {$symb}");
        }

        parent::__construct('TYPE');
    }

    /**
     * @param InterpreterContext $context
     * @param IO $io
     * @throws InternalErrorException
     * @throws FrameAccessException
     * @throws ValueException
     * @throws VariableAccessException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $symb = $context->getSymbolValue($this->symb);

        if (!$symb->isInitialized()) {
            $type = '';
        } else {
            $value = $symb->getValue();

            if (is_int($value)) {
                $type = 'int';
            } elseif (is_bool($value)) {
                $type = 'bool';
            } elseif (is_string($value)) {
                $type = 'string';
            } else {
                $type = 'nil';
            }
        }

        $context->setVariable($this->var->getText(), new Value(true, $type));
    }

    public function __toString(): string
    {
        return "{$this->getOpcode()} {$this->var} {$this->symb}";
    }
}

