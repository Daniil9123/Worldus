<?php

declare(strict_types=1);

namespace Worldus\Tests;

use PHPUnit\Framework\TestCase;
use Worldus\AuthService;
use Worldus\DatabaseInterface;

class AuthServiceTest extends TestCase
{
    public function testValidatePasswordFailsShortPassword(): void
    {
        $error = AuthService::validatePassword('Ab1!');

        $this->assertSame('Пароль должен содержать минимум 8 символов.', $error);
    }

    public function testValidatePasswordFailsMissingUppercase(): void
    {
        $error = AuthService::validatePassword('password1!');

        $this->assertSame('Пароль должен содержать хотя бы одну заглавную букву.', $error);
    }

    public function testValidatePasswordPassesValidPassword(): void
    {
        $error = AuthService::validatePassword('Worldus123!');

        $this->assertNull($error);
    }

    public function testAuthenticateReturnsNullWhenUserNotFound(): void
    {
        $db = $this->createMock(DatabaseInterface::class);
        $db->method('prepare')->willReturn(false);

        $result = AuthService::authenticate($db, 'user@example.com', 'Password1!');

        $this->assertNull($result);
    }

    public function testAuthenticateReturnsUserWhenPasswordMatches(): void
    {
        $passwordHash = password_hash('Password1!', PASSWORD_DEFAULT);

        $resultRow = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['fetch_assoc'])
            ->getMock();
        $resultRow->method('fetch_assoc')->willReturn(['id' => 1, 'password' => $passwordHash]);

        $stmt = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['bind_param', 'execute', 'get_result'])
            ->getMock();
        $stmt->expects($this->once())->method('bind_param')->with('s', 'user@example.com');
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($resultRow);

        $db = $this->createMock(DatabaseInterface::class);
        $db->method('prepare')->willReturn($stmt);

        $result = AuthService::authenticate($db, 'user@example.com', 'Password1!');

        $this->assertIsArray($result);
        $this->assertSame(1, $result['id']);
    }
}
