<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\Interpreter;
use IPP\Student\IPPType;
use IPP\Student\Uninitialized;

class PushsInstruction extends Instruction {
    /**
     * @param Argument $symb
     * @param array<string, int> $labelCache
     * @param array<array<string, string|int|bool|null|Uninitialized>> $frameStack
     * @param array<array<string, string|int|bool|null|Uninitialized>> $globalFrame
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
     * @throws InvalidArgumentException
     */
    public function __construct(protected Argument $symb,
                                array & $labelCache,
                                array & $frameStack,
                                array & $globalFrame,
                                ?array & $tempFrame,
                                array & $callStack,
                                int   & $programCounter,
                                array & $stack,
                                array & $instructions,
                                bool  & $running,
                                int   & $exitCode,
                                InputReader & $input,
                                OutputWriter & $stdout,
                                OutputWriter & $stderr)
    {
        if (!IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException("Invalid argument for PUSHS: {$symb}");
        }

        parent::__construct('PUSHS', $labelCache, $frameStack, $globalFrame,
            $tempFrame, $callStack, $programCounter, $stack, $instructions, $running, $exitCode,
            $input,
            $stdout, $stderr);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute() : void {
        $this->stack[] = $this->getSymbolValue($this->symb);
    }
};

