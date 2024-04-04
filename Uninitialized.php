<?php

namespace IPP\Student;

/**
    @brief Objekt této třídy představuje neinicializovanou hodnotu.
 */
class Uninitialized
{
    public function __toString() : string
    {
        return '(uninitialized_value)';
    }
}
