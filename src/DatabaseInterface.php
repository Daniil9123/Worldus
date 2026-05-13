<?php

declare(strict_types=1);

namespace Worldus;

interface DatabaseInterface
{
    public function query(string $sql): mixed;
    public function prepare(string $sql);
    public function getInsertId(): int;
}
