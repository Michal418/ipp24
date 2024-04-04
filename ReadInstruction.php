<?php

namespace IPP\Student;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;

class ReadInstruction extends Instruction {
    /**
     * @param Argument $var
     * @param Argument $type
     * @throws InvalidArgumentException
     */
    public function __construct(Argument $var, Argument $type)
    {
        if ($var->getIppType() !== IPPType::VAR || $type->getIppType() != IPPType::TYPE) {
            throw new InvalidArgumentException("Invalid arguments for READ: {$var}, {$type}");
        }

        parent::__construct('READ', [$var, $type]);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(Interpreter $interpreter) : void {
        $interpreter->read($this->getArguments()[0]->getText(), $this->getArguments()[1]->getText());
    }
};

