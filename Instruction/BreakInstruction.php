<?php

namespace IPP\Student\Instruction;

use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Student\Uninitialized;

class BreakInstruction extends Instruction {

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
        parent::__construct('BREAK', $labelCache, $frameStack, $globalFrame,
            $tempFrame, $callStack, $programCounter, $stack, $instructions, $running, $exitCode,
            $input, $stdout, $stderr);
    }

    public function execute() : void {
        $this->stderr->writeString('Program counter: ' . $this->programCounter . PHP_EOL);
        $this->stderr->writeString('Global frame: ' . print_r($this->globalFrame, true) . PHP_EOL);
        $this->stderr->writeString('Local frame: ' . print_r((!empty($this->frameStack)) ? $this->frameStack[0] : 'empty', true) . PHP_EOL);
        $this->stderr->writeString('Temporary frame: ' . print_r($this->tempFrame, true) . PHP_EOL);
    }
}
