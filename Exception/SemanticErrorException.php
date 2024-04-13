<?php

namespace IPP\Student\Exception;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

class SemanticErrorException extends IPPException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct("Semantic error: $message", ReturnCode::SEMANTIC_ERROR, $previous);
    }
}