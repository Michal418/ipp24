<?php

namespace IPP\Student;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\ReturnCode;
use IPP\Student\Exception\InterpreterRuntimeException;
use IPP\Student\Exception\SourceStructureException;
use IPP\Student\Instruction\Instruction;


/**
    @brief Třída interpretu IPPcode24.
 */
class Interpreter extends AbstractInterpreter
{
    /**
     * @var array<string, int> $labelCache
     */
    private array $labelCache = [];

    /**
     * @var array<array<string, string|int|bool|null|Uninitialized>> $frameStack
     */
    private array $frameStack = [];
    /**
     * @var array<string, int|string|bool|null|Uninitialized> $globalFrame
     */
    private array $globalFrame = [];
    /**
     * @var ?array<string, int|string|bool|null|Uninitialized> $tempFrame
     */
    private ?array $tempFrame = null;
    /**
     * @var int[] $callStack
     */
    private array $callStack = [];
    private int $programCounter = 0;
    /**
     * @var array<int|string|bool|null> $stack
     */
    private array $stack = [];

    /**
     * @var array<Instruction> $instructions
     */
    private array $instructions = [];

    private bool $running = false;
    private int $exitCode = 0;

    protected function running() : bool {
        return $this->running;
    }

    /**
     * @throws SourceStructureException
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function execute(): int
    {
        $dom = $this->source->getDOMDocument();
        $this->instructions = $this->parseXml($dom);
        $this->running = true;

        $instructionCount = count($this->instructions);
        while ($this->running() && $this->programCounter < $instructionCount) {
            $instruction = $this->instructions[$this->programCounter];
            $instruction->execute();
            $this->programCounter++;
        }

        return $this->exitCode;
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
        $factory = new InstructionFactory($this->labelCache, $this->frameStack,
            $this->globalFrame, $this->tempFrame, $this->callStack,
            $this->programCounter, $this->stack, $this->instructions,
            $this->running, $this->exitCode, $this->input,
            $this->stdout, $this->stderr);

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
