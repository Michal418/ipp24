<?php

namespace IPP\Student;
use InvalidArgumentException;
use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Student\Instruction\AddInstruction;
use IPP\Student\Instruction\AndInstruction;
use IPP\Student\Instruction\BreakInstruction;
use IPP\Student\Instruction\CallInstruction;
use IPP\Student\Instruction\ConcatInstruction;
use IPP\Student\Instruction\CreateFrameInstruction;
use IPP\Student\Instruction\DprintInstruction;
use IPP\Student\Instruction\EqInstruction;
use IPP\Student\Instruction\ExitInstruction;
use IPP\Student\Instruction\GetCharInstruction;
use IPP\Student\Instruction\GtInstruction;
use IPP\Student\Instruction\IdivInstruction;
use IPP\Student\Instruction\Instruction;
use IPP\Student\Instruction\JumpIfEqInstruction;
use IPP\Student\Instruction\JumpIfNeqInstruction;
use IPP\Student\Instruction\JumpInstruction;
use IPP\Student\Instruction\LabelInstruction;
use IPP\Student\Instruction\LtInstruction;
use IPP\Student\Instruction\MoveInstruction;
use IPP\Student\Instruction\MulInstruction;
use IPP\Student\Instruction\NotInstruction;
use IPP\Student\Instruction\OrInstruction;
use IPP\Student\Instruction\PopFrameInstruction;
use IPP\Student\Instruction\PopsInstruction;
use IPP\Student\Instruction\PushFrameInstruction;
use IPP\Student\Instruction\PushsInstruction;
use IPP\Student\Instruction\ReadInstruction;
use IPP\Student\Instruction\ReturnInstruction;
use IPP\Student\Instruction\SetCharInstruction;
use IPP\Student\Instruction\Str2IntInstruction;
use IPP\Student\Instruction\StrlenInstruction;
use IPP\Student\Instruction\DefvarInstruction;
use IPP\Student\Instruction\SubInstruction;
use IPP\Student\Instruction\Int2CharInstruction;
use IPP\Student\Instruction\TypeInstruction;
use IPP\Student\Instruction\WriteInstruction;

class InstructionFactory
{
    /**
     * @param array<string, int> $labelCache
     * @param array<array<string, string|int|bool|null|Uninitialized>> $frameStack
     * @param array<array<string, string|int|bool|null|Uninitialized>> $globalFrame
     * @param ?array<string, int|string|bool|null|Uninitialized> $tempFrame
     * @param array<int> $callStack
     * @param int $programCounter
     * @param array<int|string|bool|null> $stack
     * @param Instruction[] $instructions
     * @param bool $running
     * @param int $exitCode
     * @param InputReader $input
     * @param OutputWriter $stdout
     * @param OutputWriter $stderr
     */
    public function __construct(protected array       & $labelCache,
                                protected array       & $frameStack,
                                protected array       & $globalFrame,
                                protected ?array      & $tempFrame,
                                protected array       & $callStack,
                                protected int         & $programCounter,
                                protected array       & $stack,
                                protected array       & $instructions,
                                protected bool        & $running,
                                protected int         & $exitCode,
                                protected InputReader & $input,
                                protected OutputWriter & $stdout,
                                protected OutputWriter & $stderr)
    {
    }

    /**
     * Vytvoří objekt instrukce
     *
     * @param string $opcode
     * @param Argument[] $arguments
     * @return Instruction
     * @throws InvalidArgumentException
     */
    public function create(string $opcode, array $arguments) : Instruction
    {
        switch ($opcode) {
        case 'DEFVAR':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('DEFVAR instruction expects exactly 1 argument');
            }
            return new DefvarInstruction($arguments[0], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'PUSHFRAME':
            return new PushFrameInstruction($this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'CREATEFRAME':
            return new CreateFrameInstruction($this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'POPFRAME':
            return new PopFrameInstruction($this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'CALL':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('CALL instruction expects exactly 1 argument');
            }
            return new CallInstruction($arguments[0], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'RETURN':
            return new ReturnInstruction($this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'MOVE':
            if (count($arguments) !== 2) {
                throw new InvalidArgumentException('MOVE instruction expects exactly 2 arguments');
            }
            return new MoveInstruction($arguments[0], $arguments[1], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'PUSHS':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('PUSHS instruction expects exactly 1 argument');
            }
            return new PushsInstruction($arguments[0], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'POPS':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('POPS instruction expects exactly 1 argument');
            }
            return new PopsInstruction($arguments[0], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'ADD':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('ADD instruction expects exactly 3 arguments');
            }
            return new AddInstruction($arguments[0], $arguments[1], $arguments[2], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'SUB':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('SUB instruction expects exactly 3 arguments');
            }
            return new SubInstruction($arguments[0], $arguments[1], $arguments[2], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'MUL':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('MUL instruction expects exactly 3 arguments');
            }
            return new MulInstruction($arguments[0], $arguments[1], $arguments[2], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'IDIV':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('IDIV instruction expects exactly 3 arguments');
            }
            return new IdivInstruction($arguments[0], $arguments[1], $arguments[2], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'LT':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('LT instruction expects exactly 3 arguments');
            }
            return new LtInstruction($arguments[0], $arguments[1], $arguments[2], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'GT':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('GT instruction expects exactly 3 arguments');
            }
            return new GtInstruction($arguments[0], $arguments[1], $arguments[2], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'EQ':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('EQ instruction expects exactly 3 arguments');
            }
            return new EqInstruction($arguments[0], $arguments[1], $arguments[2], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'AND':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('AND instruction expects exactly 3 arguments');
            }
            return new AndInstruction($arguments[0], $arguments[1], $arguments[2], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'OR':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('OR instruction expects exactly 3 arguments');
            }
            return new OrInstruction($arguments[0], $arguments[1], $arguments[2], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'NOT':
            if (count($arguments) !== 2) {
                throw new InvalidArgumentException('NOT instruction expects exactly 2 arguments');
            }
            return new NotInstruction($arguments[0], $arguments[1], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'INT2CHAR':
            if (count($arguments) !== 2) {
                throw new InvalidArgumentException('INT2CHAR instruction expects exactly 2 arguments');
            }
            return new Int2CharInstruction($arguments[0], $arguments[1], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'STRI2INT':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('STRI2INT instruction expects exactly 3 arguments');
            }
            return new Str2IntInstruction($arguments[0], $arguments[1], $arguments[2], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'READ':
            if (count($arguments) !== 2) {
                throw new InvalidArgumentException('READ instruction expects exactly 2 arguments');
            }
            return new ReadInstruction($arguments[0], $arguments[1], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'WRITE':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('WRITE instruction expects exactly 1 argument');
            }
            return new WriteInstruction($arguments[0], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'CONCAT':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('CONCAT instruction expects exactly 3 arguments');
            }
            return new ConcatInstruction($arguments[0], $arguments[1], $arguments[2], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'STRLEN':
            if (count($arguments) !== 2) {
                throw new InvalidArgumentException('STRLEN instruction expects exactly 2 arguments');
            }
            return new StrlenInstruction($arguments[0], $arguments[1], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'GETCHAR':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('GETCHAR instruction expects exactly 3 arguments');
            }
            return new GetCharInstruction($arguments[0], $arguments[1], $arguments[2], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'SETCHAR':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('SETCHAR instruction expects exactly 3 arguments');
            }
            return new SetCharInstruction($arguments[0], $arguments[1], $arguments[2], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'TYPE':
            if (count($arguments) !== 2) {
                throw new InvalidArgumentException('TYPE instruction expects exactly 2 arguments');
            }
            return new TypeInstruction($arguments[0], $arguments[1], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'LABEL':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('LABEL instruction expects exactly 1 argument');
            }
            return new LabelInstruction($arguments[0], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'JUMP':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('JUMP instruction expects exactly 1 argument');
            }
            return new JumpInstruction($arguments[0], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'JUMPIFEQ':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('JUMPIFEQ instruction expects exactly 3 arguments');
            }
            return new JumpIfEqInstruction($arguments[0], $arguments[1], $arguments[2], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'JUMPIFNEQ':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('JUMPIFNEQ instruction expects exactly 3 arguments');
            }
            return new JumpIfNeqInstruction($arguments[0], $arguments[1], $arguments[2], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'EXIT':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('EXIT instruction expects exactly 1 argument');
            }
            return new ExitInstruction($arguments[0], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'DPRINT':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('DPRINT instruction expects exactly 1 argument');
            }
            return new DprintInstruction($arguments[0], $this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        case 'BREAK':
            if (count($arguments) !== 0) {
                throw new InvalidArgumentException('BREAK instruction expects exactly 0 arguments');
            }
            return new BreakInstruction($this->labelCache, $this->frameStack,
                $this->globalFrame, $this->tempFrame, $this->callStack,
                $this->programCounter, $this->stack, $this->instructions,
                $this->running, $this->exitCode, $this->input,
                $this->stdout, $this->stderr);

        default:
            throw new InvalidArgumentException('Unknown opcode ' . $opcode);
        }
    }
}
