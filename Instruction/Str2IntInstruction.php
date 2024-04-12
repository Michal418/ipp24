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

class Str2IntInstruction extends Instruction {
    /**
     * @param Argument $var
     * @param Argument $symb1
     * @param Argument $symb2
     */
    public function __construct(protected Argument $var,
                                protected  Argument $symb1,
                                protected Argument $symb2)
    {
        if ($var->getIppType() !== IPPType::VAR || !IPPType::isVarOrData($symb1->getIppType()) || !IPPType::isVarOrData($symb2->getIppType())) {
            throw new InvalidArgumentException("Invalid arguments for STR2INT: $var, $symb1 $symb2");
        }

        parent::__construct('STR2INT');
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(InterpreterContext & $context, IO $io) : void {
        $string = $context->getSymbolValue($this->symb1);
        $index = $context->getSymbolValue($this->symb2);

        if (is_object($string) || is_object($index)) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, 'Attempt to read uninitialized value');
        }

        if (!is_string($string) || !is_int($index)) {
            $stringType = gettype($string);
            $indexType = gettype($index);
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type for STR2INT: ($stringType) '$string', ($indexType) '$index'.");
        }

        $len = mb_strlen($string, encoding: 'UTF-8');
        if ($index < 0 || $index >= $len) {
            throw new InterpreterRuntimeException(ReturnCode::STRING_OPERATION_ERROR,
                "Invalid index for STR2INT: string='$string' of len $len, index='$index'.");
        }

        $mbChar = mb_substr($string, $index, 1, encoding: 'UTF-8');
        $result = mb_ord($mbChar, encoding: 'UTF-8');
        $context->setVariable($this->var->getText(), $result);
    }

    public function __toString() : string {
        return "{$this->getOpcode()} {$this->var} {$this->symb1} {$this->symb2}";
    }
};

