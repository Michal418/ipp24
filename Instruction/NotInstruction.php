<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class NotInstruction extends Instruction {
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

    public function execute(InterpreterContext & $context, IO $io) : void {
        $frame = $context->getFrame($this->var->getText());
        $value = $context->getSymbolValue($this->symb);

        if (is_object($value) ) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, 'Attempt to read uninitialized value');
        }

        if (!is_bool($value)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Incompatible type for logical NOT: $value.");
        }

        $varName = $context->getVariableName($this->var->getText());
        $context->selectFrame($frame)[$varName] = !$value;
    }

    public function __toString() : string {
        return "{$this->getOpcode()} {$this->var} {$this->symb}";
    }
};

