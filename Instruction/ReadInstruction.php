<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\ReturnCode;
use IPP\Student\Argument;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\Exception\TypeException;
use IPP\Student\Exception\ValueException;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;
use IPP\Student\Value;

class ReadInstruction extends Instruction
{
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
     * @param InterpreterContext $context
     * @param IO $io
     * @throws InternalErrorException
     * @throws TypeException
     * @throws FrameAccessException
     * @throws ValueException
     */
    public function execute(InterpreterContext &$context, IO $io): void
    {
        $t = $this->type->getText();

        $value = match ($t) {
            'int' => $io->readInt(),
            'bool' => $io->readBool(),
            'string' => $io->readString(),
            default => throw new TypeException("Invalid type: '$t'"),
        };

        $context->setVariable($this->var->getText(), new Value(true, $value));
    }

    public function __toString(): string
    {
        return "{$this->getOpcode()} {$this->var} {$this->type}";
    }
}

