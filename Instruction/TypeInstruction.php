<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class TypeInstruction extends Instruction {
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
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(InterpreterContext & $context, IO $io) : void {
        $value = $context->getSymbolValue($this->symb);
        if (is_int($value)) {
            $type = 'int';
        }
        elseif (is_bool($value)) {
            $type = 'bool';
        }
        elseif (is_string($value)) {
            $type = 'string';
        }
        elseif (is_null($value)) {
            $type = 'nil';
        }
        elseif (is_a($value, 'IPP\Student\Uninitialized')) {
            $type = '';
        }
        else {
            $valueType = gettype($value);
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type: ($valueType) '$value'.");
        }
        $context->setVariable($this->var->getText(), $type);
    }

    public function __toString() : string {
        return "{$this->getOpcode()} {$this->var} {$this->symb}";
    }
};

