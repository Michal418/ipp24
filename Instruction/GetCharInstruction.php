<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\StringOperationException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;
use IPP\Student\Value;

class GetCharInstruction extends Instruction
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
            throw new InvalidArgumentException("Invalid arguments for GETCHAR: {$var}, {$symb1}, {$symb2}");
        }

        parent::__construct('GETCHAR');
    }

    /**
     * @throws InternalErrorException
     * @throws StringOperationException
     * @throws VariableAccessException
     * @throws ValueException
     * @throws FrameAccessException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $string = $context->getSymbolValue($this->symb1)->getString();
        $index = $context->getSymbolValue($this->symb2)->getInt();

        if ($index < 0 || $index >= mb_strlen($string, encoding: 'UTF-8')) {
            throw new StringOperationException("Invalid index for GETCHAR: $string, $index.");
        }

        $char = mb_substr($string, $index, 1, encoding: 'UTF-8');
        $context->setVariable($this->var->getText(), new Value(true, $char));
    }

    public function __toString(): string
    {
        return "{$this->getOpcode()} {$this->var} {$this->symb1} {$this->symb2}";
    }
}

