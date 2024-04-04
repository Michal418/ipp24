<?php

namespace IPP\Student;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;


/**
    @brief chyba při běhu interpretu způsobené sémantickou chybou ve zdroji.
 */
class InterpreterRuntimeException extends IPPException
{
    public function __construct(int $code, string $message)
    {
        $_message = match ($code) {
            ReturnCode::SEMANTIC_ERROR => "Semantic error",
            ReturnCode::OPERAND_TYPE_ERROR => "Operand type error",
            ReturnCode::VARIABLE_ACCESS_ERROR => "Variable access error",
            ReturnCode::FRAME_ACCESS_ERROR => "Frame access error",
            ReturnCode::VALUE_ERROR => "Value error",
            ReturnCode::OPERAND_VALUE_ERROR => "Operand value error",
            ReturnCode::STRING_OPERATION_ERROR => "String operation error",
            default => "error " . $code,
        };

        $_message = $_message . ": " . $message;

        parent::__construct($_message, $code);
    }
}
