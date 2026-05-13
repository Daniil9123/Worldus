<?php

declare(strict_types=1);

namespace Worldus;

class MysqliDatabase implements DatabaseInterface
{
    private \mysqli $conn;

    public function __construct(\mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function query(string $sql): mixed
    {
        return $this->conn->query($sql);
    }

    public function prepare(string $sql)
    {
        return $this->conn->prepare($sql);
    }

    public function getInsertId(): int
    {
        return $this->conn->insert_id;
    }
}
