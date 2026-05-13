<?php

declare(strict_types=1);

namespace Worldus\Tests;

use PHPUnit\Framework\TestCase;
use Worldus\MysqliDatabase;

class IntegrationTestCase extends TestCase
{
    protected MysqliDatabase $db;
    protected \mysqli $conn;

    protected function setUp(): void
    {
        // Подключение к тестовой БД
        $this->conn = new \mysqli('localhost', 'root', '', 'worldus_test');
        if ($this->conn->connect_error) {
            $this->fail('Не удалось подключиться к тестовой БД: ' . $this->conn->connect_error);
        }
        $this->db = new MysqliDatabase($this->conn);

        // Очистка таблиц перед каждым тестом
        $this->conn->query('SET FOREIGN_KEY_CHECKS = 0');
        $this->conn->query('TRUNCATE TABLE progress');
        $this->conn->query('TRUNCATE TABLE answers');
        $this->conn->query('TRUNCATE TABLE questions');
        $this->conn->query('TRUNCATE TABLE levels');
        $this->conn->query('TRUNCATE TABLE categories');
        $this->conn->query('TRUNCATE TABLE users');
        $this->conn->query('SET FOREIGN_KEY_CHECKS = 1');
    }

    protected function tearDown(): void
    {
        $this->conn->close();
    }
}