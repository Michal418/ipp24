<?php

namespace IPP\Student;

/**
    @brief Třída reprezentující instrukci.
 */
abstract class Instruction {
    /**
     * @param Argument[] $arguments
     */
    public function __construct(
        private readonly string $opcode,
        protected array $arguments)
    {
    }

    /**
     * @return Argument[]
     */
    public function getArguments() : array
    {
        return $this->arguments;
    }

    public function getOpcode() : string
    {
        return $this->opcode;
    }

    abstract public function execute(Interpreter $interpreter) : void;
}


