<?php

namespace IPP\Student;
use InvalidArgumentException;

enum IPPType {
    case INT;
    case BOOL;
    case STRING;
    case NIL;
    case LABEL;
    case TYPE;
    case VAR;

    static function isVarOrData(IPPType $ipptype) : bool {
        return IPPType::isDataType($ipptype) || $ipptype === IPPType::VAR;
    }

    static function isDataType(IPPType $ipptype) : bool {
        return in_array($ipptype, [IPPType::INT, IPPType::BOOL, IPPType::STRING, IPPType::NIL]);
    }

    /**
     * @param string $value
     * @return IPPType
     * @throw InvalidArgumentException
     */
    static function fromString(string $value) : IPPType {
        return match ($value) {
            'int' => IPPType::INT,
            'bool' => IPPType::BOOL,
            'string' => IPPType::STRING,
            'nil' => IPPType::NIL,
            'label' => IPPType::LABEL,
            'type' => IPPType::TYPE,
            'var' => IPPType::VAR,
            default => throw new InvalidArgumentException("'$value' is not IPP type")
        };
    }

    static function toString(IPPType $ipptype) : string {
        return match ($ipptype) {
            IPPType::INT => 'int',
            IPPType::BOOL => 'bool',
            IPPType::STRING => 'string',
            IPPType::NIL => 'nil',
            IPPType::LABEL => 'label',
            IPPType::TYPE => 'type',
            IPPType::VAR => 'var',
            default => throw new InvalidArgumentException("'$ipptype' is not IPP type")
        };
    }
}
