<?php

namespace IPP\Student\Instruction;

use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;
use IPP\Student\Interpreter;
use IPP\Student\Uninitialized;

class AndInstruction extends Instruction {
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

        parent::__construct('AND');
    }

    /**
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function execute(InterpreterContext & $context, IO $io) : void {
        $frame = $context->getFrame($this->var->getText());
        $value1 = $context->getSymbolValue($this->symb1);
        $value2 = $context->getSymbolValue($this->symb2);
        if (!is_bool($value1) || !is_bool($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Incompatible types for logical AND: $value1, $value2.");
        }
        $varName = $context->getVariableName($this->var->getText());
        $context->selectFrame($frame)[$varName] = $value1 && $value2;
    }
}

