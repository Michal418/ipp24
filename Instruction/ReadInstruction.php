<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\Interpreter;
use IPP\Student\IPPType;
use IPP\Student\Uninitialized;

class ReadInstruction extends Instruction {
    /**
     * @param Argument $var
     * @param Argument $type
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
    public function __construct(protected Argument $var,
                                protected Argument $type,
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
        if ($var->getIppType() !== IPPType::VAR || $type->getIppType() != IPPType::TYPE) {
            throw new InvalidArgumentException("Invalid arguments for READ: {$var}, {$type}");
        }

        parent::__construct('READ', $labelCache, $frameStack, $globalFrame,
            $tempFrame, $callStack, $programCounter, $stack, $instructions, $running, $exitCode,
            $input, $stdout, $stderr);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute() : void {
        $t = $this->type->getText();

        $value = match ($t) {
            'int' => $this->input->readInt(),
            'bool' => $this->input->readBool(),
            'string' => $this->input->readString(),
            default => throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Invalid type: '$t'"),
        };
        $this->setVariable($this->var->getText(), $value);
    }
};

