<?php

namespace IPP\Student;

use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\ReturnCode;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\Instruction\Instruction;

class InterpreterContext
{
    /**
     * @var array<string, int> $labelCache
     */
    public array $labelCache = [];

    /**
     * @var array<array<string, string|int|bool|null|Uninitialized>> $frameStack
     */
    public array $frameStack = [];
    /**
     * @var array<string, int|string|bool|null|Uninitialized> $globalFrame
     */
    public array $globalFrame = [];
    /**
     * @var ?array<string, int|string|bool|null|Uninitialized> $tempFrame
     */
    public ?array $tempFrame = null;
    /**
     * @var int[] $callStack
     */
    public array $callStack = [];
    public int $programCounter = 0;
    /**
     * @var array<int|string|bool|null> $stack
     */
    public array $stack = [];
    public bool $running = true;
    public int $exitCode = 0;

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function eq(Argument $symb1, Argument $symb2) : bool {
        $value1 = $this->getSymbolValue($symb1);
        $value2 = $this->getSymbolValue($symb2);

        if (is_object($value1) || is_object($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Attempt to read uninitialized value");
        }

        if  (gettype($value1) !== gettype($value2) && !is_null($value1) && !is_null($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Incompatible types for comparison: $value1, $value2.");
        }

        return $value1 === $value2;
    }

    /**
     * @brief Vrátí hodnotu literálu.
     * @param string $symbol
     * @param IPPType $ipptype
     * @return int|bool|string|null
     * @throws InternalErrorException
     */
    public static function parseLiteral(string $symbol, IPPType $ipptype) : int|bool|string|null
    {
        switch ($ipptype) {
            case IPPType::INT:
                if (!is_numeric($symbol)) {
                    throw new InternalErrorException('Invalid integer literal.');
                }
                return (int)$symbol;

            case IPPType::BOOL:
                if ($symbol !== 'true' && $symbol !== 'false') {
                    throw new InternalErrorException('Invalid boolean literal.');
                }
                return $symbol === 'true';

            case IPPType::STRING:
                return $symbol;

            case IPPType::NIL:
                if ($symbol !== 'nil') {
                    throw new InternalErrorException('Invalid nil literal.');
                }
                return null;

            default:
                throw new InternalErrorException("$ipptype->name is not a literal type.");
        }
    }

    /**
     * @brief Vrátí název rámce z názvu proměnné.
     * @param string $variable
     * @return string
     * @throws InternalErrorException
     */
    public static function getFrame(string $variable) : string
    {
        if (preg_match('/^(GF|LF|TF)@/', $variable, $matches)) {
            return $matches[1];
        }

        throw new InternalErrorException('Invalid variable name format: "' . $variable . '"');
    }

    /**
     * @brief Odstraní z názvu proměnné název rámce a znak @.
     * @param string $variable
     * @return string
     * @throws InternalErrorException
     */
    public static function getVariableName(string $variable) : string
    {
        if (preg_match('/^[GLT]F@(.+)$/', $variable, $matches)) {
            return $matches[1];
        }

        throw new InternalErrorException('Invalid variable name format: "' .$variable .'"');
    }

    /**
     * @brief Vybere rámec podle jména (LF, GF, TF).
     * @param string $framename
     * @return array<string, int|string|bool|null|Uninitialized>
     * @throws InterpreterRuntimeException|InternalErrorException
     */
    public function &selectFrame(string $framename) : array {
        if ($framename === 'LF') {
            if (empty($this->frameStack)) {
                throw new InterpreterRuntimeException(ReturnCode::FRAME_ACCESS_ERROR, "Frame stack is empty.");
            }
            return $this->frameStack[count($this->frameStack) - 1];
        }
        elseif ($framename === 'GF') {
            return $this->globalFrame;
        }
        elseif ($framename === 'TF') {
            if (is_null($this->tempFrame)) {
                throw new InterpreterRuntimeException(ReturnCode::FRAME_ACCESS_ERROR, "Temporary frame is null.");
            }
            return $this->tempFrame;
        }
        else {
            throw new InternalErrorException("Unknown frame name $framename.");
        }
    }

    /**
     * @brief Vrátí hodnotu proměnné nebo konstanty.
     * @param Argument $symbol
     * @return int|string|bool|null|Uninitialized
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function getSymbolValue(Argument $symbol) : int|string|bool|null|Uninitialized {
        if ($symbol->getIppType() === IPPType::VAR) {
            $varCode = $symbol->getText();
            $frame = $this->getFrame($varCode);
            $varName = $this->getVariableName($varCode);

            if (!array_key_exists($varName, $this->selectFrame($frame))) {
                throw new InterpreterRuntimeException(ReturnCode::VARIABLE_ACCESS_ERROR, "Variable $varName not found in frame $frame.");
            }

            return $this->selectFrame($frame)[$varName];
        }
        else {
            return $this->parseLiteral($symbol->getText(), $symbol->getIppType());
        }
    }

    /**
     * @brief Nastaví hodnotu proměnné.
     * @param string $varCode
     * @param int|string|bool|null|Uninitialized $value
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function setVariable(string $varCode, int|string|null|bool|Uninitialized $value) : void {
        if ($value instanceof Uninitialized) {
            throw new InterpreterRuntimeException(ReturnCode::INTERNAL_ERROR, "Cannot set variable to uninitialized value.");
        }

        $frame = $this->getFrame($varCode);
        $varName = $this->getVariableName($varCode);

        if (!array_key_exists($varName, $this->selectFrame($frame))) {
            throw new InterpreterRuntimeException(ReturnCode::VARIABLE_ACCESS_ERROR, "Variable $varName not found in frame $frame.");
        }

        $this->selectFrame($frame)[$varName] = $value;
    }

    /**
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function defvar(string $varCode) : void {
        $varName = $this->getVariableName($varCode);
        $frame = $this->getFrame($varCode);
        if (array_key_exists($varName, $this->selectFrame($frame))) {
            throw new InterpreterRuntimeException(ReturnCode::SEMANTIC_ERROR, "Variable $varCode already defined.");
        }

        $frame = $this->getFrame($varCode);
        $varName = $this->getVariableName($varCode);

        $this->selectFrame($frame)[$varName] = new Uninitialized();
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function pushframe() : void {
        if (is_null($this->tempFrame)) {
            throw new InterpreterRuntimeException(ReturnCode::FRAME_ACCESS_ERROR, "Temporary frame is null.");
        }
        $this->frameStack[] = $this->tempFrame;
        $this->tempFrame = null;
    }

    public function createframe() : void {
        $this->tempFrame = [];
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function popframe() : void {
        if (empty($this->frameStack)) {
            throw new InterpreterRuntimeException(ReturnCode::FRAME_ACCESS_ERROR, "Frame stack is empty.");
        }
        $this->tempFrame = array_pop($this->frameStack);
    }

    /**
     * @brief Najde index instrukce podle návěští.
     * @param string $label
     * @return int
     * @throws InterpreterRuntimeException
     */
    public function findLabel(string $label) : int
    {
        if (array_key_exists($label, $this->labelCache)) {
            return $this->labelCache[$label];
        }

        throw new InterpreterRuntimeException(ReturnCode::SEMANTIC_ERROR, "Label '$label' not found.");
    }
}