<?php

namespace IPP\Student;
use InvalidArgumentException;

class InstructionFactory
{
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
            return new DefvarInstruction($arguments[0]);

        case 'PUSHFRAME':
            return new PushFrameInstruction();

        case 'CREATEFRAME':
            return new CreateFrameInstruction();

        case 'POPFRAME':
            return new PopFrameInstruction();

        case 'CALL':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('CALL instruction expects exactly 1 argument');
            }
            return new CallInstruction($arguments[0]);

        case 'RETURN':
            return new ReturnInstruction();

        case 'MOVE':
            if (count($arguments) !== 2) {
                throw new InvalidArgumentException('MOVE instruction expects exactly 2 arguments');
            }
            return new MoveInstruction($arguments[0], $arguments[1]);

        case 'PUSHS':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('PUSHS instruction expects exactly 1 argument');
            }
            return new PushsInstruction($arguments[0]);

        case 'POPS':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('POPS instruction expects exactly 1 arguemnts');
            }
            return new PopsInstruction($arguments[0]);

        case 'ADD':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('ADD instruction expects exactly 3 arguments');
            }
            return new AddInstruction($arguments[0], $arguments[1], $arguments[2]);

        case 'SUB':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('SUB instruction expects exactly 3 arguments');
            }
            return new SubInstruction($arguments[0], $arguments[1], $arguments[2]);

        case 'MUL':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('MUL instruction expects exactly 3 arguments');
            }
            return new MulInstruction($arguments[0], $arguments[1], $arguments[2]);

        case 'IDIV':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('IDIV instruction expects exactly 3 arguments');
            }
            return new IdivInstruction($arguments[0], $arguments[1], $arguments[2]);

        case 'LT':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('LT instruction expects exactly 3 arguments');
            }
            return new LtInstruction($arguments[0], $arguments[1], $arguments[2]);

        case 'GT':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('GT instruction expects exactly 3 arguments');
            }
            return new GtInstruction($arguments[0], $arguments[1], $arguments[2]);

        case 'EQ':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('EQ instruction expects exactly 3 arguments');
            }
            return new EqInstruction($arguments[0], $arguments[1], $arguments[2]);

        case 'AND':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('AND instruction expects exactly 3 arguments');
            }
            return new AndInstruction($arguments[0], $arguments[1], $arguments[2]);

        case 'OR':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('OR instruction expects exactly 3 arguments');
            }
            return new OrInstruction($arguments[0], $arguments[1], $arguments[2]);

        case 'NOT':
            if (count($arguments) !== 2) {
                throw new InvalidArgumentException('NOT instruction expects exactly 2 arguments');
            }
            return new NotInstruction($arguments[0], $arguments[1]);

        case 'INT2CHAR':
            if (count($arguments) !== 2) {
                throw new InvalidArgumentException('INT2CHAR instruction expects exactly 2 arguments');
            }
            return new Int2CharInstruction($arguments[0], $arguments[1]);

        case 'STR2INT':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('STRI2INT instruction expects exactly 3 arguments');
            }
            return new Str2IntInstruction($arguments[0], $arguments[1]);

        case 'READ':
            if (count($arguments) !== 2) {
                throw new InvalidArgumentException('READ instruction expects exactly 2 arguments');
            }
            return new ReadInstruction($arguments[0], $arguments[1]);

        case 'WRITE':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('WRITE instruction expects exactly 1 argument');
            }
            return new WriteInstruction($arguments[0]);

        case 'CONCAT':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('CONCAT instruction expects exactly 3 arguments');
            }
            return new ConcatInstruction($arguments[0], $arguments[1], $arguments[2]);

        case 'STRLEN':
            if (count($arguments) !== 2) {
                throw new InvalidArgumentException('STRLEN instruction expects exactly 2 arguments');
            }
            return new StrlenInstruction($arguments[0], $arguments[1]);

        case 'GETCHAR':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('GETCHAR instruction expects exactly 3 arguments');
            }
            return new GetCharInstruction($arguments[0], $arguments[1], $arguments[2]);

        case 'SETCHAR':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('SETCHAR instruction expects exactly 3 arguments');
            }
            return new SetCharInstruction($arguments[0], $arguments[1], $arguments[2]);

        case 'TYPE':
            if (count($arguments) !== 2) {
                throw new InvalidArgumentException('TYPE instruction expects exactly 2 arguments');
            }
            return new TypeInstruction($arguments[0], $arguments[1]);

        case 'LABEL':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('LABEL instruction expects exactly 1 argument');
            }
            return new LabelInstruction($arguments[0]);

        case 'JUMP':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('JUMP instruction expects exactly 1 argument');
            }
            return new JumpInstruction($arguments[0]);

        case 'JUMPIFEQ':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('JUMPIFEQ instruction expects exactly 3 arguments');
            }
            return new JumpIfEqInstruction($arguments[0], $arguments[1], $arguments[2]);

        case 'JUMPIFNEQ':
            if (count($arguments) !== 3) {
                throw new InvalidArgumentException('JUMPIFNEQ instruction expects exactly 3 arguments');
            }
            return new JumpIfNeqInstruction($arguments[0], $arguments[1], $arguments[2]);

        case 'EXIT':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('EXIT instruction expects exactly 1 argument');
            }
            return new ExitInstruction($arguments[0]);

        case 'DPRINT':
            if (count($arguments) !== 1) {
                throw new InvalidArgumentException('DPRINT instruction expects exactly 1 argument');
            }
            return new DprintInstruction($arguments[0]);

        case 'BREAK':
            if (count($arguments) !== 0) {
                throw new InvalidArgumentException('BREAK instruction expects exactly 0 arguments');
            }
            return new BreakInstruction();

        default:
            throw new InvalidArgumentException('Unknown opcode ' . $opcode);
        }
    }
}
