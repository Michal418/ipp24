<?php

namespace IPP\Student;

use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;

class IO
{
    public function __construct(
        protected InputReader  $reader,
        protected OutputWriter $stdout,
        protected OutputWriter $stderr)
    {
    }

    public function readInt(): ?int
    {
        return $this->reader->readInt();
    }

    public function readBool(): ?bool
    {
        return $this->reader->readBool();
    }

    public function readString(): ?string
    {
        return $this->reader->readString();
    }

    public function errBool(bool $value): void
    {
        $this->stderr->writeBool($value);
    }

    public function writeBool(bool $value): void
    {
        $this->stdout->writeBool($value);
    }

    public function errInt(int $value): void
    {
        $this->stderr->writeInt($value);
    }

    public function writeInt(int $value): void
    {
        $this->stdout->writeInt($value);
    }

    public function errString(string $value): void
    {
        $this->stderr->writeString($value);
    }

    public function writeString(string $value): void
    {
        $this->stdout->writeString($value);
    }
}