<?php

declare(strict_types=1);

namespace Worldus\Tests;

use Worldus\AuthService;

/**
 * @group integration
 */
class AuthServiceIntegrationTest extends IntegrationTestCase
{
    public function testRegisterAndAuthenticate(): void
    {
        // Регистрация
        $result = AuthService::register($this->db, 'testuser@example.com', 'Valid@Pass123');
        $this->assertTrue($result);

        // Аутентификация
        $user = AuthService::authenticate($this->db, 'testuser@example.com', 'Valid@Pass123');
        $this->assertIsArray($user);
        $this->assertEquals('testuser@example.com', $user['email']);

        // Неверный пароль
        $user = AuthService::authenticate($this->db, 'testuser@example.com', 'wrongpass');
        $this->assertNull($user);
    }

    public function testGetUserRole(): void
    {
        // Создать пользователя
        $this->conn->query("INSERT INTO users (email, password, role) VALUES ('adminuser@example.com', 'hash', 'admin')");
        $userId = $this->conn->insert_id;

        // Проверить роль
        $role = AuthService::getUserRole($this->db, $userId);
        $this->assertEquals('admin', $role);

        $role = AuthService::getUserRole($this->db, 999);
        $this->assertNull($role);
    }
}