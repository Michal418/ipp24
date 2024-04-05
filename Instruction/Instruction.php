<?php

namespace IPP\Student\Instruction;

use IPP\Core\Exception\InternalErrorException;
use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\Instruction\LabelInstruction;
use IPP\Student\IPPType;
use IPP\Student\Uninitialized;

/**
    * @brief Třída reprezentující instrukci.
 */
abstract class Instruction {
    /**
     * @param string $opcode
     * @param array<string, int> $labelCache
     * @param array<array<string, string|int|bool|null|Uninitialized>> $frameStack
     * @param array<string, int|string|bool|null|Uninitialized> $globalFrame
     * @param ?array<string, int|string|bool|null|Uninitialized> $tempFrame
     * @param int[] $callStack
     * @param int $programCounter
     * @param array<int|string|bool|null> $stack
     * @param Instruction[] $instructions
     * @param bool $running
     * @param int $exitCode
     * @param InputReader $input
     * @param OutputWriter $stdout
     * @param OutputWriter $stderr
     */
    public function __construct(
        private readonly string $opcode,
        protected array & $labelCache,
        protected array & $frameStack,
        protected array & $globalFrame,
        protected ?array & $tempFrame,
        protected array & $callStack,
        protected int   & $programCounter,
        protected array & $stack,
        protected array & $instructions,
        protected bool  & $running,
        protected int   & $exitCode,
        protected InputReader & $input,
        protected OutputWriter & $stdout,
        protected OutputWriter & $stderr)
    {
    }

    public function getOpcode() : string
    {
        return $this->opcode;
    }

    /**
     * @return void
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    abstract public function execute() : void;


    /**
     * @brief Vrátí hodnotu literálu.
     * @param string $symbol
     * @param IPPType $ipptype
     * @return int|bool|string|null
     * @throws InternalErrorException
     */
    protected static function parseLiteral(string $symbol, IPPType $ipptype) : int|bool|string|null
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
    protected static function getFrame(string $variable) : string
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
    protected static function getVariableName(string $variable) : string
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
    protected function &selectFrame(string $framename) : array {
        if ($framename === 'LF') {
            if (empty($this->frameStack)) {
                throw new InterpreterRuntimeException(ReturnCode::FRAME_ACCESS_ERROR, "Frame stack is empty.");
            }
            return $this->frameStack[0];
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
    protected function getSymbolValue(Argument $symbol) : int|string|bool|null|Uninitialized {
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
    protected function setVariable(string $varCode, int|string|null|bool|Uninitialized $value) : void {
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
    protected function defvar(string $varCode) : void {
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
    protected function pushframe() : void {
        if (is_null($this->tempFrame)) {
            throw new InterpreterRuntimeException(ReturnCode::FRAME_ACCESS_ERROR, "Temporary frame is null.");
        }
        $this->frameStack[] = $this->tempFrame;
        $this->tempFrame = null;
    }

    protected function createframe() : void {
        $this->tempFrame = [];
    }

    /**
     * @throws InterpreterRuntimeException
     */
    protected function popframe() : void {
        if (empty($this->frameStack)) {
            throw new InterpreterRuntimeException(ReturnCode::FRAME_ACCESS_ERROR, "Frame stack is empty.");
        }
        $this->tempFrame = array_pop($this->frameStack);
    }

    /**
     * @brief Najde index instrukce podle návěští.
     * @param string $label
     * @param Instruction[] $instructions
     * @return int
     * @throws InterpreterRuntimeException
     */
    protected function findLabel(string $label, array $instructions) : int
    {
        if (array_key_exists($label, $this->labelCache)) {
            return $this->labelCache[$label];
        }

        foreach ($instructions as $index => $instruction) {
            if (is_a($instruction, 'IPP\Student\Instruction\LabelInstruction') && $instruction->getLabel() === $label) {
                $this->labelCache[$label] = $index;
                return $index;
            }
        }

        throw new InterpreterRuntimeException(ReturnCode::SEMANTIC_ERROR, "Label '$label' not found.");
    }
}


