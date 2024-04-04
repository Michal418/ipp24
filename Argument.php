<?php

namespace IPP\Student;

/**
    @brief Třída reprezentující argument instrukce.
 */
readonly class Argument {
    public function __construct(
        private IPPType $ipptype,
        private string  $text)
    {
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
        $ipptypestr = IPPType::toString($this->ipptype);
        return "Argument(type=$ipptypestr, text=$this->text)";
    }
}
