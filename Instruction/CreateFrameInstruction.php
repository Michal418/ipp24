<?php

namespace IPP\Student\Instruction;


use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Student\Interpreter;
use IPP\Student\Uninitialized;

class CreateFrameInstruction extends Instruction {
    /**
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
    public function __construct(array        & $labelCache,
                                array        & $frameStack,
                                array        & $globalFrame,
                                ?array       & $tempFrame,
                                array        & $callStack,
                                int          & $programCounter,
                                array        & $stack,
                                array        & $instructions,
                                bool         & $running,
                                int          & $exitCode, InputReader & $input,
                                OutputWriter & $stdout,
                                OutputWriter & $stderr)
    {
        parent::__construct('CREATEFRAME', $labelCache, $frameStack, $globalFrame,
            $tempFrame, $callStack, $programCounter, $stack, $instructions, $running, $exitCode,
            $input, $stdout, $stderr);
    }

    public function execute() : void {
        $this->createFrame();
    }
};

