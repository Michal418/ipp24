<?php

namespace IPP\Student;

use IPP\Core\Exception\InternalErrorException;
use IPP\Core\ReturnCode;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\SemanticErrorException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\Instruction\Instruction;

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

    public function __construct()
    {
        $this->globalFrame = new Frame();
    }

    public function & getTemporaryFrame(): ?Frame
    {
        return $this->tempFrame;
    }

    public function & getGlobalFrame(): Frame
    {
        return $this->globalFrame;
    }

    /**
     * @throws ValueException
     */
    public function popStack(): Value
    {
        if (empty($this->stack)) {
            throw new ValueException("Stack is empty.");
        }

        return array_pop($this->stack);
    }

    public function pushStack(Value $value): void
    {
        $this->stack[] = $value;
    }

    public function pushCallStack(int $value): void
    {
        $this->callStack[] = $value;
    }

    /**
     * @throws ValueException
     */
    public function popCallStack(): int
    {
        if (empty($this->callStack)) {
            throw new ValueException("Call stack is empty.");
        }
        return array_pop($this->callStack);
    }

    public function getProgramCounter(): int
    {
        return $this->programCounter;
    }

    public function setProgramCounter(int $value): void
    {
        $this->programCounter = $value;
    }

    public function incrementProgramCounter(): void
    {
        $this->programCounter++;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    public function setExitCode(int $value): void
    {
        $this->exitCode = $value;
    }

    public function stop(): void
    {
        $this->running = false;
    }

    /**
     *
     * Inicializuje tabulku, která mapuje názvy návěstí na index instrukce
     * @param Instruction[] $instructions
     * @throws SemanticErrorException
     */
    public function initializeLabelMap(array $instructions): void
    {
        foreach ($instructions as $index => $instruction) {
            if (!is_a($instruction, 'IPP\Student\Instruction\LabelInstruction')) {
                continue;
            }

            if (array_key_exists($instruction->getLabel(), $this->labelMap)) {
                throw new SemanticErrorException("Label redefinition: '{$instruction->getLabel()}'.");
            }

            $this->labelMap[$instruction->getLabel()] = $index;
        }
    }

    /**
     * @throws InternalErrorException
     */
    public function eq(Argument $symb1, Argument $symb2): bool
    {
        $value1 = $this->getSymbolValue($symb1);
        $value2 = $this->getSymbolValue($symb2);

        $result = $value1->eq($value2);
        $value = $result->getValue();

        if (!is_bool($value)) {
            throw new InternalErrorException('This code should be unreachable');
        }

        return $value;
    }

    /**
     * @brief Vrátí hodnotu proměnné nebo konstanty.
     * @param Argument $symbol
     * @return Value
     * @throws VariableAccessException
     * @throws InternalErrorException
     * @throws ValueException
     */
    public function getSymbolValue(Argument $symbol): Value
    {
        if ($symbol->getIppType() === IPPType::VAR) {
            $varCode = $symbol->getText();
            $frame = $this->getFrame($varCode);
            $varName = $this->getVariableName($varCode);

            if (!$this->selectFrame($frame)->isSymbolDefined($varName)) {
                throw new VariableAccessException("Variable '$varName' is not defined in frame '$frame'.");
            }

            return $this->selectFrame($frame)->getSymbolValue($varName);
        } else {
            return $this->parseLiteral($symbol->getText(), $symbol->getIppType());
        }
    }

    /**
     * @brief Vrátí název rámce z názvu proměnné.
     * @param string $variable
     * @return string
     * @throws InternalErrorException
     */
    public static function getFrame(string $variable): string
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
    public static function getVariableName(string $variable): string
    {
        if (preg_match('/^[GLT]F@(.+)$/', $variable, $matches)) {
            return $matches[1];
        }

        throw new InternalErrorException('Invalid variable name format: "' . $variable . '"');
    }

    /**
     * @brief Vybere rámec podle jména (LF, GF, TF).
     * @param string $framename
     * @return Frame
     * @throws InternalErrorException
     * @throws FrameAccessException
     */
    public function &selectFrame(string $framename): Frame
    {
        if ($framename === 'LF') {
            if (empty($this->frameStack)) {
                throw new FrameAccessException("Frame stack is empty.");
            }
            return $this->frameStack[count($this->frameStack) - 1];
        } elseif ($framename === 'GF') {
            return $this->globalFrame;
        } elseif ($framename === 'TF') {
            if (is_null($this->tempFrame)) {
                throw new FrameAccessException("Temporary frame is null.");
            }
            return $this->tempFrame;
        } else {
            throw new InternalErrorException("Unknown frame name $framename.");
        }
    }

    /**
     * @brief Vrátí hodnotu literálu.
     * @param string $symbol
     * @param IPPType $ipptype
     * @return Value
     * @throws InternalErrorException
     */
    public static function parseLiteral(string $symbol, IPPType $ipptype): Value
    {
        switch ($ipptype) {
            case IPPType::INT:
                if (!is_numeric($symbol)) {
                    throw new InternalErrorException('Invalid integer literal.');
                }
                return new Value(true, (int)$symbol);

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
     * @brief Nastaví hodnotu proměnné.
     * @param string $varCode
     * @param Value $value
     * @throws FrameAccessException
     * @throws InternalErrorException
     * @throws ValueException
     * @throws VariableAccessException
     */
    public function setVariable(string $varCode, Value $value): void
    {
        if (!$value->isInitialized()) {
            throw new InternalErrorException("Cannot set variable to uninitialized value.");
        }

        $frame = $this->getFrame($varCode);
        $varName = $this->getVariableName($varCode);

        if (!$this->selectFrame($frame)->isSymbolDefined($varName)) {
            throw new VariableAccessException("Variable '$varName' is not defined in frame '$frame'.");
        }

        $this->selectFrame($frame)->setSymbol($varName, $value);
    }

    /**
     * @throws InternalErrorException
     * @throws FrameAccessException
     * @throws ValueException
     * @throws SemanticErrorException
     */
    public function defvar(string $varCode): void
    {
        $varName = $this->getVariableName($varCode);
        $frame = $this->getFrame($varCode);

        if ($this->selectFrame($frame)->isSymbolDefined($varName)) {
            throw new SemanticErrorException("Variable '$varCode' already defined.");
        }

        $frame = $this->getFrame($varCode);
        $varName = $this->getVariableName($varCode);

        $this->selectFrame($frame)->defineSymbol($varName);
    }

    /**
     * @throws FrameAccessException
     */
    public function pushframe(): void
    {
        if (is_null($this->tempFrame)) {
            throw new FrameAccessException("Temporary frame is null.");
        }
        $this->frameStack[] = $this->tempFrame;
        $this->tempFrame = null;
    }

    public function createframe(): void
    {
        $this->tempFrame = new Frame();
    }

    /**
     * @throws FrameAccessException
     */
    public function popframe(): void
    {
        if (empty($this->frameStack)) {
            throw new FrameAccessException("Frame stack is empty.");
        }
        $this->tempFrame = array_pop($this->frameStack);
    }

    /**
     * @brief Najde index instrukce podle návěští.
     * @param string $label
     * @return int
     * @throws SemanticErrorException
     */
    public function findLabel(string $label): int
    {
        if (array_key_exists($label, $this->labelMap)) {
            return $this->labelMap[$label];
        }

        throw new SemanticErrorException("Label '$label' not found.");
    }
}
