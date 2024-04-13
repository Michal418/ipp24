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
use IPP\Student\Value;

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

        if (!$value->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Attempt to read uninitialized value");
        }

        $primitiveValue = $value->getValue();

        if (!is_int($primitiveValue)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR,
                "Invalid type for INT2CHAR: $value.");
        }

        if ($primitiveValue < 0 || $primitiveValue > 1_114_111) {
            throw new InterpreterRuntimeException(ReturnCode::STRING_OPERATION_ERROR,
                "Invalid value for INT2CHAR: $value.");
        }

        $result = mb_chr($primitiveValue, encoding: 'UTF-8');
        $context->setVariable($this->var->getText(), new Value(true, $result));
    }

    public function __toString() : string {
        return "{$this->getOpcode()} {$this->var} {$this->symb}";
    }
};

