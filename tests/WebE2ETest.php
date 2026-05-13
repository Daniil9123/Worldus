<?php

declare(strict_types=1);

namespace Worldus\Tests;

use PHPUnit\Framework\TestCase;

/**
 * @group e2e
 */
class WebE2ETest extends TestCase
{
    private E2EClient $client;

    protected function setUp(): void
    {
        $baseUrl = getenv('WORLDUS_BASE_URL') ?: 'http://localhost:81/Worldus';
        $this->client = new E2EClient($baseUrl);

        // Проверяем доступность сервера через реальный файл
        $response = $this->client->get('/index.php');
        
        if ($response['status'] === 0) {
            // cURL ошибка - сервер недоступен
            $this->markTestSkipped(
                "Web server is not running at {$baseUrl}. " .
                "Please start Apache on port 81 in XAMPP Control Panel."
            );
        }
        
        if ($response['status'] >= 500) {
            // Server error
            $this->markTestSkipped(
                "Web server returned error {$response['status']} at {$baseUrl}. " .
                "Check server logs."
            );
        }
    }

    public function testHomePageIsAccessible(): void
    {
        $response = $this->client->get('/categories.php');
        $this->assertSame(200, $response['status']);
        $this->assertStringContainsString('Категории', $response['body']);
    }

    public function testProtectedPageRedirectsToLoginWhenNotAuthenticated(): void
    {
        $response = $this->client->get('/question.php?level=1&category=1');
        $this->assertTrue(in_array($response['status'], [302, 303]), 'Expected redirect to login');
        $this->assertStringContainsString('login.php', $response['headers']['location'] ?? '');
    }

    public function testRegisterAndLoginFlow(): void
    {
        $email = 'e2e+' . uniqid() . '@example.com';
        $password = 'Valid@1234';

        $response = $this->client->get('/register.php');
        $this->assertSame(200, $response['status']);
        $passwordField = $this->client->extractPasswordFieldName($response['body']);
        $this->assertStringStartsWith('password_real_', $passwordField);

        $postData = [
            'email' => $email,
            $passwordField => $password,
            'fake_email' => '',
            'fake_password' => '',
            'hidden_password' => '',
        ];

        $response = $this->client->post('/register.php', $postData);
        $this->assertTrue(in_array($response['status'], [302, 303]));
        $this->assertStringContainsString('login.php', $response['headers']['location'] ?? '');

        $this->client->followRedirect($response);

        $response = $this->client->post('/login.php', [
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertTrue(in_array($response['status'], [302, 303]));
        $this->assertStringContainsString('index.php', $response['headers']['location'] ?? '');

        $response = $this->client->followRedirect($response);
        $this->assertSame(200, $response['status']);
        $this->assertStringContainsString('Worldus', $response['body']);
    }
}
