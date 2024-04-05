<?php

namespace IPP\Student\Exception;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;

/**
    @brief Neočekávaná struktura XML souboru.
 */
class SourceStructureException extends IPPException
{
    public function __construct(string $message)
    {
        parent::__construct($message, ReturnCode::INVALID_SOURCE_STRUCTURE);
    }
}

