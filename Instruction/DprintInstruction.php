<?php

namespace IPP\Student\Instruction;


use InvalidArgumentException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Student\Argument;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\Interpreter;
use IPP\Student\InterpreterContext;
use IPP\Student\IO;
use IPP\Student\IPPType;
use IPP\Student\Uninitialized;

class DprintInstruction extends Instruction {
    /**
     * @param Argument $symb
     */
    public function __construct(protected Argument $symb)
    {
        if (!IPPType::isVarOrData($symb->getIppType())) {
            throw new InvalidArgumentException("Invalid argument for DPRINT: {$symb}");
        }

        parent::__construct('DPRINT');
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(InterpreterContext & $context, IO $io) : void {
        $value = $context->getSymbolValue($this->symb);
        $io->errString((string) $value);
    }
}
