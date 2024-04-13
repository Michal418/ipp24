<?php

namespace IPP\Student;

use IPP\Core\Exception\InternalErrorException;
use IPP\Core\ReturnCode;
use IPP\Student\Exception\InterpreterRuntimeException;

class InterpreterContext
{
    /**
     * @var array<string, int> $labelMap
     */
    private array $labelMap = [];

    /**
     * @var Frame[] $frameStack
     */
    private array $frameStack = [];

    private Frame $globalFrame;

    private ?Frame $tempFrame = null;

    /**
     * @var int[] $callStack
     */
    private array $callStack = [];

    private int $programCounter = 0;

    /**
     * @var Value[] $stack
     */
    private array $stack = [];

    private bool $running = true;

    private int $exitCode = 0;

    public function __construct() {
        $this->globalFrame = new Frame();
    }

    /**
     * @return array<string, int>
     */
    public function & getLabelMap() : array {
        return $this->labelMap;
    }

    public function & getTemporaryFrame() : ?Frame {
        return $this->tempFrame;
    }

    public function & getGlobalFrame() : Frame {
        return $this->globalFrame;
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function popStack() : Value {
        if (empty($this->stack)) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Stack is empty.");
        }

        return array_pop($this->stack);
    }

    public function pushStack(Value $value) : void {
        $this->stack[] = $value;
    }

    public function pushCallStack(int $value) : void {
        $this->callStack[] = $value;
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function popCallStack() : int {
        if (empty($this->callStack)) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Call stack is empty.");
        }
        return array_pop($this->callStack);
    }

    public function getProgramCounter() : int {
        return $this->programCounter;
    }

    public function setProgramCounter(int $value) : void {
        $this->programCounter = $value;
    }

    public function incrementProgramCounter() : void {
        $this->programCounter++;
    }

    public function isRunning() : bool {
        return $this->running;
    }

    public function getExitCode() : int {
        return $this->exitCode;
    }

    public function setExitCode(int $value) : void {
        $this->exitCode = $value;
    }

    public function stop() : void {
        $this->running = false;
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function eq(Argument $symb1, Argument $symb2) : bool {
        $value1 = $this->getSymbolValue($symb1);
        $value2 = $this->getSymbolValue($symb2);

        $result = $value1->eq($value2);
        $value = $result->getValue();

        if (!is_bool($value)) {
            throw new InterpreterRuntimeException(ReturnCode::INTERNAL_ERROR,
                'This code should be unreachable');
        }

        return $value;
    }

    /**
     * @brief Vrátí hodnotu literálu.
     * @param string $symbol
     * @param IPPType $ipptype
     * @throws InternalErrorException
     */
    public static function parseLiteral(string $symbol, IPPType $ipptype) : Value
    {
        switch ($ipptype) {
            case IPPType::INT:
                if (!is_numeric($symbol)) {
                    throw new InternalErrorException('Invalid integer literal.');
                }
                return new Value(true, (int) $symbol);

            case IPPType::BOOL:
                if ($symbol !== 'true' && $symbol !== 'false') {
                    throw new InternalErrorException('Invalid boolean literal.');
                }
                return new Value(true, $symbol === 'true');

            case IPPType::STRING:
                return new Value(true, $symbol);

            case IPPType::NIL:
                if ($symbol !== 'nil') {
                    throw new InternalErrorException('Invalid nil literal.');
                }
                return new Value(true, null);

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
     * @throws InterpreterRuntimeException|InternalErrorException
     */
    public function &selectFrame(string $framename) : Frame {
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
                throw new InterpreterRuntimeException(ReturnCode::FRAME_ACCESS_ERROR,
                    "Temporary frame is null.");
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
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function getSymbolValue(Argument $symbol) : Value {
        if ($symbol->getIppType() === IPPType::VAR) {
            $varCode = $symbol->getText();
            $frame = $this->getFrame($varCode);
            $varName = $this->getVariableName($varCode);

            if (!$this->selectFrame($frame)->isSymbolDefined($varName)) {
                throw new InterpreterRuntimeException(ReturnCode::VARIABLE_ACCESS_ERROR,
                    "Variable '$varName' is not defined in frame '$frame'.");
            }

            return $this->selectFrame($frame)->getSymbolValue($varName);
        }
        else {
            return $this->parseLiteral($symbol->getText(), $symbol->getIppType());
        }
    }

    /**
     * @brief Nastaví hodnotu proměnné.
     * @param string $varCode
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function setVariable(string $varCode, Value $value) : void {
        if (!$value->isInitialized()) {
            throw new InterpreterRuntimeException(ReturnCode::INTERNAL_ERROR, "Cannot set variable to uninitialized value.");
        }

        $frame = $this->getFrame($varCode);
        $varName = $this->getVariableName($varCode);

        if (!$this->selectFrame($frame)->isSymbolDefined($varName)) {
            throw new InterpreterRuntimeException(ReturnCode::VARIABLE_ACCESS_ERROR,
                "Variable '$varName' is not defined in frame '$frame'.");
        }

        $this->selectFrame($frame)->setSymbol($varName, $value);
    }

    /**
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function defvar(string $varCode) : void {
        $varName = $this->getVariableName($varCode);
        $frame = $this->getFrame($varCode);

        if ($this->selectFrame($frame)->isSymbolDefined($varName)) {
            throw new InterpreterRuntimeException(ReturnCode::SEMANTIC_ERROR,
                "Variable '$varCode' already defined.");
        }

        $frame = $this->getFrame($varCode);
        $varName = $this->getVariableName($varCode);

        $this->selectFrame($frame)->defineSymbol($varName);
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
        $this->tempFrame = new Frame();
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
        if (array_key_exists($label, $this->labelMap)) {
            return $this->labelMap[$label];
        }

        throw new InterpreterRuntimeException(ReturnCode::SEMANTIC_ERROR, "Label '$label' not found.");
    }
}