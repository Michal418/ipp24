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
        switch ($code) {
        case ReturnCode::SEMANTIC_ERROR:
            $_message = "Semantic error";
            break;
        case ReturnCode::OPERAND_TYPE_ERROR:
            $_message = "Operand type error";
            break;
        case ReturnCode::VARIABLE_ACCESS_ERROR:
            $_message = "Variable access error";
            break;
        case ReturnCode::FRAME_ACCESS_ERROR:
            $_message = "Frame access error";
            break;
        case ReturnCode::VALUE_ERROR:
            $_message = "Value error";
            break;
        case ReturnCode::OPERAND_VALUE_ERROR:
            $_message = "Operand value error";
            break;
        case ReturnCode::STRING_OPERATION_ERROR:
            $_message = "String operation error";
            break;
        default:
            $_message = "error " . $code;
        }

        $_message = $_message . ": " . $message;

        parent::__construct($_message, $code);
    }
}
