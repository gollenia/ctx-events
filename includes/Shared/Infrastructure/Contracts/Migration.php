<?php

namespace Contexis\Events\Shared\Infrastructure\Contracts;

interface Migration
{
    public function getTableName(): string;
    public function getColumns(): array;
    public function getColumnsAsString(): string;
}
