<?php

namespace IPP\Student\Instruction;


use IntlChar;
use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class Int2CharInstruction extends Instruction {
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
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(InterpreterContext & $context, IO $io) : void {
        $value = $context->getSymbolValue($this->symb);

        if (is_object($value)) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Attempt to read uninitialized value");
        }

        if (!is_int($value)) {
            $valueType = gettype($value);
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type for INT2CHAR: ($valueType) '$value'.");
        }

        if ($value < 0 || $value > 1_114_111) {
            throw new InterpreterRuntimeException(ReturnCode::STRING_OPERATION_ERROR, "Invalid value for INT2CHAR: $value.");
        }

        $result = mb_chr($value, encoding: 'UTF-8');
        $context->setVariable($this->var->getText(), $result);
    }

    public function __toString() : string {
        return "{$this->getOpcode()} {$this->var} {$this->symb}";
    }
};

