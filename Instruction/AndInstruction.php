<?php

namespace IPP\Student\Instruction;

use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\IPPType;
use IPP\Student\Interpreter;
use IPP\Student\Uninitialized;

class AndInstruction extends Instruction {
    /**
     * @param Argument $var
     * @param Argument $symb1
     * @param Argument $symb2
     * @param array<string, int> $labelCache
     * * @param array<array<string, string|int|bool|null|Uninitialized>> $frameStack
     * * @param array<array<string, string|int|bool|null|Uninitialized>> $globalFrame
     * * @param ?array<string, int|string|bool|null|Uninitialized> $tempFrame
     * * @param int[] $callStack
     * * @param int $programCounter
     * * @param array<int|string|bool|null> $stack
     * * @param Instruction[] $instructions
     * * @param bool $running
     * * @param int $exitCode
     * * @param InputReader $input
     * * @param OutputWriter $stdout
     * * @param OutputWriter $stderr
     */
    public function __construct(
                                protected Argument $var,
                                protected Argument $symb1,
                                protected Argument $symb2,
                                array & $labelCache,
                                array & $frameStack,
                                array & $globalFrame,
                                ?array & $tempFrame,
                                array & $callStack,
                                int   & $programCounter,
                                array & $stack,
                                array & $instructions,
                                bool  & $running,
                                int   & $exitCode,InputReader & $input,
                                OutputWriter & $stdout,
                                OutputWriter & $stderr)
    {
        if ($var->getIppType() !== IPPType::VAR || !IPPType::isVarOrData($symb1->getIppType()) || !IPPType::isVarOrData($symb2->getIppType())) {
            throw new InvalidArgumentException("Invalid arguments for ADD: {$var}, {$symb1}, {$symb2}");
        }

        parent::__construct('AND', $labelCache, $frameStack, $globalFrame,
            $tempFrame, $callStack, $programCounter, $stack, $instructions, $running, $exitCode,
        $input, $stdout, $stderr);
    }

    /**
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function execute() : void {
        $frame = $this->getFrame($this->var->getText());
        $value1 = $this->getSymbolValue($this->symb1);
        $value2 = $this->getSymbolValue($this->symb2);
        if (!is_bool($value1) || !is_bool($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Incompatible types for logical AND: $value1, $value2.");
        }
        $varName = $this->getVariableName($this->var->getText());
        $this->selectFrame($frame)[$varName] = $value1 && $value2;
    }
}

