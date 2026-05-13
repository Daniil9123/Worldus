<?php

declare(strict_types=1);

namespace Worldus;

class AuthService
{
    public static function validatePassword(string $password): ?string
    {
        if (strlen($password) < 8) {
            return 'Пароль должен содержать минимум 8 символов.';
        }

        if (!preg_match('/[a-z]/', $password)) {
            return 'Пароль должен содержать хотя бы одну строчную букву.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return 'Пароль должен содержать хотя бы одну заглавную букву.';
        }

        if (!preg_match('/[0-9]/', $password)) {
            return 'Пароль должен содержать хотя бы одну цифру.';
        }

        if (!preg_match('/[\W_]/', $password)) {
            return 'Пароль должен содержать хотя бы один специальный символ.';
        }

        return null;
    }

    public static function authenticate(DatabaseInterface $db, string $email, string $password): ?array
    {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;

        if (!$user) {
            return null;
        }

        if (password_verify($password, $user['password'])) {
            return $user;
        }

        return null;
    }

    public static function register(DatabaseInterface $db, string $email, string $plainPassword): bool|string
    {
        $error = self::validatePassword($plainPassword);
        if ($error !== null) {
            return $error;
        }

        $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);

        $stmt = $db->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        if (!$stmt) {
            return 'Не удалось подготовить запрос.';
        }

        $stmt->bind_param('ss', $email, $passwordHash);

        if ($stmt->execute()) {
            return true;
        }

        return 'Пользователь уже существует.';
    }

    public static function getUserRole(DatabaseInterface $db, int $userId): ?string
    {
        $stmt = $db->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;

        return $row['role'] ?? null;
    }
}
