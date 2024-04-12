<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;

class ReadInstruction extends Instruction {
    /**
     * @param Argument $var
     * @param Argument $type
     */
    public function __construct(protected Argument $var,
                                protected Argument $type)
    {
        if ($var->getIppType() !== IPPType::VAR || $type->getIppType() != IPPType::TYPE) {
            throw new InvalidArgumentException("Invalid arguments for READ: {$var}, {$type}");
        }

        parent::__construct('READ');
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(InterpreterContext & $context, IO $io) : void {
        $t = $this->type->getText();

        $value = match ($t) {
            'int' => $io->readInt(),
            'bool' => $io->readBool(),
            'string' => $io->readString(),
            default => throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type: '$t'"),
        };

        $context->setVariable($this->var->getText(), $value);
    }

    public function __toString() : string {
        return "{$this->getOpcode()} {$this->var} {$this->type}";
    }
};

