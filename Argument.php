<?php

namespace IPP\Student;

use InvalidArgumentException;
use IPP\Student\Exception\SourceStructureException;

/**
    * @brief Třída reprezentující argument instrukce.
 */
readonly class Argument {
    public function __construct(
        private IPPType $ipptype,
        private string  $text)
    {
        switch ($ipptype) {
            case IPPType::INT:
                if (!is_numeric($text) || (((float) $text) !== ((float) ((int) $text)))) {
                    throw new InvalidArgumentException("Invalid int literal: $text");
                }
                break;
            case IPPType::NIL:
                if ($text !== 'nil') {
                    throw new InvalidArgumentException("Invalid nil literal: $text");
                }
                break;
            case IPPType::BOOL:
                if ($text !== 'true' && $text !== 'false') {
                    throw new InvalidArgumentException("Invalid bool literal: $text");
                }
                break;
            case IPPType::VAR:
                if (!str_starts_with($text, 'LF@') && !str_starts_with($text, 'TF@') && !str_starts_with($text, 'GF@')) {
                    throw new InvalidArgumentException("Lexical error: $text");
                }
                break;
            case IPPType::STRING:
            case IPPType::LABEL:
                break;
            case IPPType::TYPE:
                if (!in_array($text, ['int', 'bool', 'string', 'nil'])) {
                    throw new InvalidArgumentException("Invalid type: $text");
                }
                break;
        }
    }

    public function getIppType() : IPPType
    {
        return $this->ipptype;
    }

    public function getText() : string
    {
        return $this->text;
    }

    public function __toString() : string
    {
        $str = IPPType::toString($this->ipptype);
        return "Argument(type=$str, text=$this->text)";
    }
}
