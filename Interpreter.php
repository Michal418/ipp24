<?php

namespace IPP\Student;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use IPP\Student\Exception\SemanticErrorException;
use IPP\Student\Exception\SourceStructureException;
use IPP\Student\Instruction\Instruction;


/**
 * @brief Třída interpretu IPPcode24.
 */
class Interpreter extends AbstractInterpreter
{
    /**
     * @return int
     * @throws IPPException
     */
    public function execute(): int
    {
        $context = new InterpreterContext();
        $io = new IO($this->input, $this->stdout, $this->stderr);
        $instructions = $this->readInstructions();
        $context->initializeLabelMap($instructions);

        $instructionCount = count($instructions);
        while ($context->isRunning() && $context->getProgramCounter() < $instructionCount) {
            $instruction = $instructions[$context->getProgramCounter()];
            $instruction->execute($context, $io);
            $context->incrementProgramCounter();
        }

        return $context->getExitCode();
    }

    /**
     * @return Instruction[]
     * @throws SourceStructureException
     */
    private function readInstructions(): array
    {
        $dom = $this->source->getDOMDocument();
        return $this->parseXml($dom);
    }

    /**
     * @param DOMDocument $dom
     * @return Instruction[]
     * @throws SourceStructureException
     */
    private function parseXml(DOMDocument $dom): array
    {
        /**
         * @var Instruction[] $instructions
         */
        $instructions = [];
        $previousOrder = 0;
        $factory = new InstructionFactory();

        $root = $dom->getRootNode();
        $children = $root->childNodes;

        if (count($children) !== 1) {
            throw new SourceStructureException('No program node or more than one program node.');
        }

        $programNode = $children[0];

        if (!($programNode instanceof DOMElement) || $programNode->nodeName !== 'program') {
            throw new SourceStructureException('Unexpected node');
        }

        if (!$programNode->hasAttribute('language') || $programNode->getAttribute('language') !== 'IPPcode24') {
            throw new SourceStructureException("Bad language");
        }

        foreach ($programNode->childNodes as $instructionNode) {
            if (!($instructionNode instanceof DOMElement)) {
                continue;
            }

            if ($instructionNode->nodeName !== 'instruction') {
                throw new SourceStructureException('Unexpected node.');
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

                if (!$argumentNode->hasAttribute('type')) {
                    throw new SourceStructureException('Invalid argument.');
                }

                $ipptypeStr = trim($argumentNode->getAttribute('type'));
                try {
                    $ipptype = IPPType::fromString($ipptypeStr);
                } catch (InvalidArgumentException $ex) {
                    throw new SourceStructureException('Invalid type string', $ex);
                }

                $text = trim($argumentNode->textContent);

                if ($ipptype === IPPType::STRING) {
                    $text = $this->unescape($text);
                }

                $index = match ($argumentNode->nodeName) {
                    'arg1' => 0,
                    'arg2' => 1,
                    'arg3' => 2,
                    default => throw new SourceStructureException('Invalid argument.')
                };

                try {
                    $argument = new Argument($ipptype, $text);
                } catch (InvalidArgumentException $ex) {
                    throw new SourceStructureException('Could not construct argument', $ex);
                }

                $arguments[$index] = $argument;
            }

            if (array_key_exists(1, $arguments) && !array_key_exists(0, $arguments)
                || array_key_exists(2, $arguments) && !array_key_exists(1, $arguments)) {
                throw new SourceStructureException('Invalid argument.');
            }

            try {
                $instruction = $factory->create($opcode, $arguments);
            } catch (InvalidArgumentException $ex) {
                throw new SourceStructureException('Could not create instruction', $ex);
            }

            $instructions[] = $instruction;
        }

        return $instructions;
    }

    /**
     * @brief Převede escape v řetězci sekvence na znaky.
     * @param string $str
     * @return string
     */
    protected static function unescape(string $str): string
    {
        $result = $str;

        if (preg_match_all('/\\\\([0-9]{3})/', $str, $matches)) {
            foreach ($matches[1] as $match) {
                $intValue = intval($match);
                $result = str_replace("\\$match", chr($intValue), $result);
            }
        }

        return $result;
    }
}
