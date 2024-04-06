<?php

namespace IPP\Student;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\Exception\SourceStructureException;
use IPP\Student\Instruction\Instruction;


/**
    @brief Třída interpretu IPPcode24.
 */
class Interpreter extends AbstractInterpreter
{
    /**
     * @return Instruction[]
     * @throws SourceStructureException
     */
    private function readInstructions() : array {
        $dom = $this->source->getDOMDocument();
        return $this->parseXml($dom);
    }

    /**
     * @throws SourceStructureException
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute() : int
    {
        $context = new InterpreterContext();
        $io = new IO($this->input, $this->stdout, $this->stderr);
        $instructions = $this->readInstructions();

        foreach ($instructions as $index => $instruction) {
            if (is_a($instruction, 'IPP\Student\Instruction\LabelInstruction')) {
                $context->labelCache[$instruction->getLabel()] = $index;
            }
        }

        $instructionCount = count($instructions);
        while ($context->running && $context->programCounter < $instructionCount) {
            $instruction = $instructions[$context->programCounter];
            $instruction->execute($context, $io);
            $context->programCounter++;
        }

        return $context->exitCode;
    }

    /**
     * @brief Převede escape v řetězci sekvence na znaky.
     * @param string $str
     * @return string
     */
    protected static function unescape(string $str) : string {
        $result = $str;

        if (preg_match_all('/\\\\([0-9]{3})/', $str, $matches)) {
            foreach ($matches[1] as $match) {
                $ival = intval($match);
                $result = str_replace("\\$match", chr($ival), $result);
            }
        }

        return $result;
    }

    /**
     * @param DOMDocument $dom
     * @return Instruction[]
     * @throws SourceStructureException
     */
    private function parseXml(DOMDocument $dom) : array
    {
        /**
         * @var Instruction[] $instructions
         */
        $instructions = [];
        $previousOrder = -1;
        $factory = new InstructionFactory();

        foreach ($dom->getElementsByTagName('instruction') as $instructionNode) {
            if (!($instructionNode instanceof DOMElement)) {
                continue;
            }

            if (!$instructionNode->hasAttribute('order') || !$instructionNode->hasAttribute('opcode')) {
                throw new SourceStructureException('Invalid instruction.');
            }

            $order = intval($instructionNode->getAttribute('order'));
            $opcode = strtoupper(trim($instructionNode->getAttribute('opcode')));

            if ($order <= $previousOrder) {
                throw new SourceStructureException('Invalid order of instructions.');
            }
            $previousOrder = $order;

            /**
             * @var Argument[] $arguments
             */
            $arguments = [];
            foreach ($instructionNode->childNodes as $argumentNode) {
                if (!($argumentNode instanceof DOMElement)) {
                    continue;
                }

                if (!in_array($argumentNode->nodeName, ['arg1', 'arg2', 'arg3'])) {
                    throw new SourceStructureException('Invalid argument.');
                }

                if (!$argumentNode->hasAttribute('type')) {
                    throw new SourceStructureException('Invalid argument.');
                }

                $ipptypeStr = trim($argumentNode->getAttribute('type'));
                try {
                    $ipptype = IPPType::fromString($ipptypeStr);
                }
                catch (InvalidArgumentException $ex) {
                    throw new SourceStructureException($ex->getMessage());
                }

                $text = trim($argumentNode->textContent);

                if ($ipptype === IPPType::STRING) {
                    $text = $this->unescape($text);
                }

                $argument = new Argument($ipptype, $text);
                $arguments[] = $argument;
            }

            try {
                $instruction = $factory->create($opcode, $arguments);
            }
            catch (InvalidArgumentException $ex)
            {
                throw new SourceStructureException($ex->getMessage());
            }

            $instructions[] = $instruction;
        }

        return $instructions;
    }
}
