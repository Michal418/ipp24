<?php

namespace IPP\Student;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\InternalErrorException;
use IPP\Core\ReturnCode;


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
     * @var int[] $callstack
     */
    private array $callstack = [];
    private int $program_counter = 0;
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

    /**
     * @brief Vybere rámec podle jména (LF, GF, TF).
     * @param string $framename
     * @return array<string, int|string|bool|null|Uninitialized>
     * @throws InterpreterRuntimeException|InternalErrorException
     */
    private function &selectFrame(string $framename) : array {
        if ($framename === 'LF') {
            if (empty($this->frameStack)) {
                throw new InterpreterRuntimeException(ReturnCode::FRAME_ACCESS_ERROR, "Frame stack is empty.");
            }
            return $this->frameStack[0];
        }
        elseif ($framename === 'GF') {
            return $this->globalFrame;
        }
        elseif ($framename === 'TF') {
            if (is_null($this->tempFrame)) {
                throw new InterpreterRuntimeException(ReturnCode::FRAME_ACCESS_ERROR, "Temporary frame is null.");
            }
            return $this->tempFrame;
        }
        else {
            throw new InternalErrorException("Unknown frame name $framename.");
        }
    }

    /**
     * @brief Vrátí hodnotu proměnné nebo konstanty.
     * @param Argument $symbol
     * @return int|string|bool|null|Uninitialized
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    private function getSymbolValue(Argument $symbol) : int|string|bool|null|Uninitialized {
        if ($symbol->getIppType() === IPPType::VAR) {
            $varCode = $symbol->getText();
            $frame = $this->getFrame($varCode);
            $varName = $this->getVariableName($varCode);

            if (!array_key_exists($varName, $this->selectFrame($frame))) {
                throw new InterpreterRuntimeException(ReturnCode::VARIABLE_ACCESS_ERROR, "Variable $varName not found in frame $frame.");
            }

            return $this->selectFrame($frame)[$varName];
        }
        else {
            return $this->parseLiteral($symbol->getText(), $symbol->getIppType());
        }
    }

    /**
     * @brief Nastaví hodnotu proměnné.
     * @param string $varCode
     * @param int|string|bool|null|Uninitialized $value
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    function setVariable(string $varCode, int|string|null|bool|Uninitialized $value) : void {
        if ($value instanceof Uninitialized) {
            throw new InterpreterRuntimeException(ReturnCode::INTERNAL_ERROR, "Cannot set variable to uninitialized value.");
        }

        $frame = $this->getFrame($varCode);
        $varName = $this->getVariableName($varCode);

        if (!array_key_exists($varName, $this->selectFrame($frame))) {
            throw new InterpreterRuntimeException(ReturnCode::VARIABLE_ACCESS_ERROR, "Variable $varName not found in frame $frame.");
        }

        $this->selectFrame($frame)[$varName] = $value;
    }

    /**
     * @brief Převede escape v řetězci sekvence na znaky.
     * @param string $str
     * @return string
     */
    private static function unescape(string $str) : string {
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
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function defvar(string $varCode) : void {
        $varName = $this->getVariableName($varCode);
        $frame = $this->getFrame($varCode);
        if (array_key_exists($varName, $this->selectFrame($frame))) {
            throw new InterpreterRuntimeException(ReturnCode::SEMANTIC_ERROR, "Variable $varCode already defined.");
        }

        $frame = $this->getFrame($varCode);
        $varName = $this->getVariableName($varCode);

        $this->selectFrame($frame)[$varName] = new Uninitialized();
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function pushframe() : void {
        if (is_null($this->tempFrame)) {
            throw new InterpreterRuntimeException(ReturnCode::FRAME_ACCESS_ERROR, "Temporary frame is null.");
        }
        $this->frameStack[] = $this->tempFrame;
        $this->tempFrame = null;
    }

    public function createframe() : void {
        $this->tempFrame = [];
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function popframe() : void {
        if (empty($this->frameStack)) {
            throw new InterpreterRuntimeException(ReturnCode::FRAME_ACCESS_ERROR, "Frame stack is empty.");
        }
        $this->tempFrame = array_pop($this->frameStack);
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function call(string $label) : void {
        $pc = $this->findLabel($label, $this->instructions);
        $this->callstack[] = $this->program_counter;
        $this->program_counter = $pc;

    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function return_instruction() : void {
        $result = array_pop($this->callstack);

        if (is_null($result)) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Call stack is empty.");
        }

        $this->program_counter = $result;

    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function move(string $varCode, Argument $symbol) : void {
        $this->setVariable($varCode, $this->getSymbolValue($symbol));
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function pushs(Argument $symbol) : void {
        $this->stack[] = $this->getSymbolValue($symbol);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function pops(string $varCode) : void {
        $result = array_pop($this->stack);
        if (is_null($result)) {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Stack is empty.");
        }
        $this->setVariable($varCode, $result);
    }

    /**
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function add(string $varCode, Argument $symbol1, Argument $symbol2) : void {
        $value1 = $this->getSymbolValue($symbol1);
        $value2 = $this->getSymbolValue($symbol2);
        if (!is_int($value1) || !is_int($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Incompatible types for addition: $value1, $value2.");
        }
        $this->setVariable($varCode, $value1 + $value2);
    }

    /**
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function sub(string $varCode, Argument $symbol1, Argument $symbol2) : void {
        $value1 = $this->getSymbolValue($symbol1);
        $value2 = $this->getSymbolValue($symbol2);
        if (!is_int($value1) || !is_int($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Incompatible types for subtraction: $value1, $value2.");
        }
        $this->setVariable($varCode, $value1 - $value2);
    }

    /**
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function mul(string $varCode, Argument $symbol1, Argument $symbol2) : void {
        $value1 = $this->getSymbolValue($symbol1);
        $value2 = $this->getSymbolValue($symbol2);
        if (!is_int($value1) || !is_int($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Incompatible types for multiplication: $value1, $value2.");
        }
        $this->setVariable($varCode, $value1 * $value2);
    }

    /**
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function idiv(string $varCode, Argument $symbol1, Argument $symbol2) : void {
        $value1 = $this->getSymbolValue($symbol1);
        $value2 = $this->getSymbolValue($symbol2);
        if (!is_int($value1) || !is_int($value2) || $value2 === 0) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_VALUE_ERROR, "Incompatible types for division: $value1, $value2.");
        }
        $this->setVariable($varCode, $value1 / $value2);
    }

    /**
     * @param string $varCode
     * @param Argument $symbol1
     * @param Argument $symbol2
     * @return array{string, string, bool|int|Uninitialized|null|string, bool|int|Uninitialized|null|string}
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    private function xt(string $varCode, Argument $symbol1, Argument $symbol2) : array {
        $frame = $this->getFrame($varCode);
        $value1 = $this->getSymbolValue($symbol1);
        $value2 = $this->getSymbolValue($symbol2);
        if (gettype($value1) !== gettype($value2) || is_bool($value1) || is_null($value1)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Incompatible types for comparison: $value1, $value2.");
        }
        $varName = $this->getVariableName($varCode);

        return [$frame, $varName, $value1, $value2];
    }

    /**
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function lt(string $varCode, Argument $symbol1, Argument $symbol2) : void {
        [$frame, $varName, $value1, $value2] = $this->xt($varCode, $symbol1, $symbol2);
        $this->selectFrame($frame)[$varName] = $value1 < $value2;
    }

    /**
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function gt(string $varCode, Argument $symbol1, Argument $symbol2) : void {
        [$frame, $varName, $value1, $value2] = $this->xt($varCode, $symbol1, $symbol2);
        $this->selectFrame($frame)[$varName] = $value1 > $value2;
    }

    /**
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function eq(string $varCode, Argument $symbol1, Argument $symbol2) : void {
        $frame = $this->getFrame($varCode);
        $varName = $this->getVariableName($varCode);
        $value1 = $this->getSymbolValue($symbol1);
        $value2 = $this->getSymbolValue($symbol2);
        if (gettype($value1) !== gettype($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Incompatible types for comparison: $value1, $value2.");
        }
        $this->selectFrame($frame)[$varName] = $value1 === $value2;
    }

    /**
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function and(string $varCode, Argument $symbol1, Argument $symbol2) : void {
        $frame = $this->getFrame($varCode);
        $value1 = $this->getSymbolValue($symbol1);
        $value2 = $this->getSymbolValue($symbol2);
        if (!is_bool($value1) || !is_bool($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Incompatible types for logical AND: $value1, $value2.");
        }
        $varName = $this->getVariableName($varCode);
        $this->selectFrame($frame)[$varName] = $value1 && $value2;
    }

    /**
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function or(string $varCode, Argument $symbol1, Argument $symbol2) : void {
        $frame = $this->getFrame($varCode);
        $value1 = $this->getSymbolValue($symbol1);
        $value2 = $this->getSymbolValue($symbol2);
        if (!is_bool($value1) || !is_bool($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Incompatible types for logical OR: $value1, $value2.");
        }
        $varName = $this->getVariableName($varCode);
        $this->selectFrame($frame)[$varName] = $value1 || $value2;
    }

    /**
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function not(string $varCode, Argument $symbol) : void {
        $frame = $this->getFrame($varCode);
        $value = $this->getSymbolValue($symbol);
        if (!is_bool($value)) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_TYPE_ERROR, "Incompatible type for logical NOT: $value.");
        }
        $varName = $this->getVariableName($varCode);
        $this->selectFrame($frame)[$varName] = !$value;
    }

    /**
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function int2char(string $varCode, Argument $symbol) : void {
        $value = $this->getSymbolValue($symbol);
        if (!is_int($value) || $value < 0 || $value > 255 /*1_114_111*/) {
            throw new InterpreterRuntimeException(ReturnCode::STRING_OPERATION_ERROR, "Invalid value for INT2CHAR: $value.");
        }
        $this->setVariable($varCode, chr($value));
    }

    /**
     * @throws InternalErrorException
     * @throws InterpreterRuntimeException
     */
    public function str2int(string $varCode, Argument $symbol1, Argument $symbol2) : void {
        $string = $this->getSymbolValue($symbol1);
        $index = $this->getSymbolValue($symbol2);
        if (!is_string($string) || $index < 0 || $index >= strlen($string)) {
            throw new InterpreterRuntimeException(ReturnCode::STRING_OPERATION_ERROR, "Invalid value for STR2INT: $string, $index.");
        }
        $this->setVariable($varCode, ord($string[$index]));
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function read(string $varCode, string $type) : void {
        $value = match ($type) {
            'int' => $this->input->readInt(),
            'bool' => $this->input->readBool(),
            'string' => $this->input->readString(),
            default => throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Invalid type."),
        };
        $this->setVariable($varCode, $value);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function write(Argument $symbol) : void {
        $value = $this->getSymbolValue($symbol);
        if (is_string($value)) {
            $this->stdout->writeString($value);
        }
        elseif (is_int($value)) {
            $this->stdout->writeInt($value);
        }
        elseif (is_bool($value)) {
            $this->stdout->writeBool($value);
        }
        elseif (is_null($value)) {
            $this->stdout->writeString('');
        }
        else {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Uninitialized variable used in WRITE.");
        }
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function concat(string $varCode, Argument $symbol1, Argument $symbol2) : void {
        $value1 = $this->getSymbolValue($symbol1);
        $value2 = $this->getSymbolValue($symbol2);
        if (!is_string($value1) || !is_string($value2)) {
            throw new InterpreterRuntimeException(ReturnCode::STRING_OPERATION_ERROR, "Invalid value for CONCAT: $value1, $value2.");
        }
        $this->setVariable($varCode, $value1 . $value2);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function strlen(string $varCode, Argument $symbol) : void {
        $value = $this->getSymbolValue($symbol);
        if (!is_string($value)) {
            throw new InterpreterRuntimeException(ReturnCode::STRING_OPERATION_ERROR, "Invalid value for STRLEN: $value.");
        }
        $this->setVariable($varCode, strlen($value));
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function getchar(string $varCode, Argument $symbol1, Argument $symbol2) : void {
        $string = $this->getSymbolValue($symbol1);
        $index = $this->getSymbolValue($symbol2);
        if (!is_string($string) || $index < 0 || $index >= strlen($string)) {
            throw new InterpreterRuntimeException(ReturnCode::STRING_OPERATION_ERROR, "Invalid value for GETCHAR: $string, $index.");
        }
        $this->setVariable($varCode, $string[$index]);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function setchar(string $varCode, Argument $symbol1, Argument $symbol2, Argument $symbol3) : void {
        $string = $this->getSymbolValue($symbol1);
        $index = $this->getSymbolValue($symbol2);
        $char = $this->getSymbolValue($symbol3);
        if (!is_string($string) || !is_int($index) || $index < 0 || $index >= strlen($string) || !is_string($char) || strlen($char) !== 1) {
            throw new InterpreterRuntimeException(ReturnCode::STRING_OPERATION_ERROR, "Invalid value for SETCHAR: $string, $index, $char.");
        }
        $string[$index] = $char;
        $this->setVariable($varCode, $string);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function type(string $varCode, Argument $symbol) : void {
        $value = $this->getSymbolValue($symbol);
        if (is_int($value)) {
            $type = 'int';
        }
        elseif (is_bool($value)) {
            $type = 'bool';
        }
        elseif (is_string($value)) {
            $type = 'string';
        }
        else {
            throw new InterpreterRuntimeException(ReturnCode::VALUE_ERROR, "Invalid type.");
        }
        $this->setVariable($varCode, $type);
    }

    public function label(string $label) : void {
        $this->labelCache[$label] = $this->program_counter;
    }

    /**
     * @throws InterpreterRuntimeException
     */
    public function jump(string $label) : void {
        $this->program_counter = $this->findLabel($label, $this->instructions);
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function jumpifeq(string $label, Argument $symbol1, Argument $symbol2) : void {
        $value1 = $this->getSymbolValue($symbol1);
        $value2 = $this->getSymbolValue($symbol2);
        if ($value1 === $value2) {
            $this->program_counter = $this->findLabel($label, $this->instructions);
        }
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function jumpifneq(string $label, Argument $symbol1, Argument $symbol2) : void {
        $value1 = $this->getSymbolValue($symbol1);
        $value2 = $this->getSymbolValue($symbol2);
        if ($value1 !== $value2) {
            $this->program_counter = $this->findLabel($label, $this->instructions);
        }
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function exit(Argument $symbol) : void {
        $exitCode = $this->getSymbolValue($symbol);
        if (!is_int($exitCode) || $exitCode < 0 || $exitCode > 9) {
            throw new InterpreterRuntimeException(ReturnCode::OPERAND_VALUE_ERROR, "Invalid value for EXIT: $exitCode.");
        }
        $this->exitCode = $exitCode;
        $this->running = false;
    }

    /**
     * @throws InterpreterRuntimeException
     * @throws InternalErrorException
     */
    public function dprint(Argument $symbol) : void {
        $value = $this->getSymbolValue($symbol);
        $this->stderr->writeString((string) $value);
    }

    public function break_instruction() : void {
        $this->stderr->writeString('Program counter: ' . $this->program_counter . PHP_EOL);
        $this->stderr->writeString('Global frame: ' . print_r($this->globalFrame, true) . PHP_EOL);
        $this->stderr->writeString('Local frame: ' . print_r((!empty($this->frameStack)) ? $this->frameStack[0] : 'empty', true) . PHP_EOL);
        $this->stderr->writeString('Temporary frame: ' . print_r($this->tempFrame, true) . PHP_EOL);
    }

    /**
     * @throws SourceStructureException
     */
    public function execute(): int
    {
        $dom = $this->source->getDOMDocument();
        $this->instructions = $this->parseXml($dom);
        $this->running = true;

        $instructionCount = count($this->instructions);
        while ($this->running && $this->program_counter < $instructionCount) {
            $instruction = $this->instructions[$this->program_counter];
            $instruction->execute($this);
            $this->program_counter++;
        }

        return $this->exitCode;
    }

    /**
     * @brief Najde index instrukce podle návěští.
     * @param string $label
     * @param Instruction[] $instructions
     * @return int
     * @throws InterpreterRuntimeException
     */
    private function findLabel(string $label, array $instructions) : int
    {
        if (array_key_exists($label, $this->labelCache)) {
            return $this->labelCache[$label];
        }

        foreach ($instructions as $index => $instruction) {
            if ($instruction->getOpcode() === 'LABEL' && $instruction->getArguments()[0]->getText() === $label) {
                $this->labelCache[$label] = $index;
                return $index;
            }
        }

        throw new InterpreterRuntimeException(ReturnCode::SEMANTIC_ERROR, "Label '$label' not found.");
    }

    /**
     * @brief Vrátí hodnotu literálu.
     * @param string $symbol
     * @param IPPType $ipptype
     * @return int|bool|string|null
     * @throws InternalErrorException
     */
    private static function parseLiteral(string $symbol, IPPType $ipptype) : int|bool|string|null
    {
        switch ($ipptype) {
            case IPPType::INT:
                if (!is_numeric($symbol)) {
                    throw new InternalErrorException('Invalid integer literal.');
                }
                return (int)$symbol;

            case IPPType::BOOL:
                if ($symbol !== 'true' && $symbol !== 'false') {
                    throw new InternalErrorException('Invalid boolean literal.');
                }
                return $symbol === 'true';

            case IPPType::STRING:
                return $symbol;

            case IPPType::NIL:
                if ($symbol !== 'nil') {
                    throw new InternalErrorException('Invalid nil literal.');
                }
                return null;

            default:
                throw new InternalErrorException("$ipptype->name is not a literal type.");
        }
    }

    /**
     * @brief Vrátí název rámce z názvu proměnné.
     * @param string $variable
     * @return string
     * @throws InternalErrorException
     */
    private static function getFrame(string $variable) : string
    {
        if (preg_match('/^(GF|LF|TF)@/', $variable, $matches)) {
            return $matches[1];
        }

        throw new InternalErrorException('Invalid variable name format: "' . $variable . '"');
    }

    /**
     * @brief Odstraní z názvu proměnné název rámce a znak @.
     * @param string $variable
     * @return string
     * @throws InternalErrorException
     */
    private static function getVariableName(string $variable) : string
    {
        if (preg_match('/^[GLT]F@(.+)$/', $variable, $matches)) {
            return $matches[1];
        }

        throw new InternalErrorException('Invalid variable name format: "' .$variable .'"');
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

                if ($argumentNode->nodeName !== 'arg1' && $argumentNode->nodeName !== 'arg2' && $argumentNode->nodeName !== 'arg3') {
                    throw new SourceStructureException('Invalid argument.');
                }

                if (!$argumentNode->hasAttribute('type')) {
                    throw new SourceStructureException('Invalid argument.');
                }

                $ipptype = IPPType::fromString(trim($argumentNode->getAttribute('type')));
                $text = trim($argumentNode->textContent);

                if ($ipptype === IPPType::STRING) {
                    $text = $this->unescape($text);
                }

                $argument = new Argument($ipptype, $text);
                $arguments[] = $argument;
            }

            if (count($arguments) > 3) {
                throw new SourceStructureException('Invalid number of arguments.');
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
