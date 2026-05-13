<?php

declare(strict_types=1);

namespace Worldus\Tests;

use Worldus\QuestionService;

/**
 * @group integration
 */
class QuestionServiceIntegrationTest extends IntegrationTestCase
{
    private QuestionService $questionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->questionService = new QuestionService($this->db);
    }

    public function testValidateLevelCategoryAndGetQuestionsByLevel(): void
    {
        // Создать категорию и уровень
        $this->conn->query("INSERT INTO categories (name, image, description) VALUES ('Test Cat', 'test.jpg', '1 уровень')");
        $categoryId = $this->conn->insert_id;
        $this->conn->query("INSERT INTO levels (category_id, title, level_order, required_level) VALUES ($categoryId, 'Level 1', 1, NULL)");
        $levelId = $this->conn->insert_id;

        // Проверить валидацию
        $valid = $this->questionService->validateLevelCategory($levelId, $categoryId);
        $this->assertTrue($valid);

        $invalid = $this->questionService->validateLevelCategory($levelId, 999);
        $this->assertFalse($invalid);

        // Добавить вопрос
        $this->conn->query("INSERT INTO questions (level_id, question_text, type) VALUES ($levelId, 'Test Question', 'text')");
        $questionId = $this->conn->insert_id;
        $this->conn->query("INSERT INTO answers (question_id, answer_text, is_correct) VALUES ($questionId, 'Answer 1', 1)");

        // Получить вопросы
        $questions = $this->questionService->getQuestionsByLevel($levelId);
        $this->assertIsArray($questions);
        $this->assertCount(1, $questions);
        $this->assertEquals('Test Question', $questions[0]['question_text']);
    }

    public function testCompleteLevel(): void
    {
        // Создать пользователя и уровень
        $this->conn->query("INSERT INTO users (email, password, role) VALUES ('testuser@example.com', 'hash', 'user')");
        $userId = $this->conn->insert_id;
        $this->conn->query("INSERT INTO categories (name, image, description) VALUES ('Test Cat', 'test.jpg', '1 уровень')");
        $categoryId = $this->conn->insert_id;
        $this->conn->query("INSERT INTO levels (category_id, title, level_order, required_level) VALUES ($categoryId, 'Level 1', 1, NULL)");
        $levelId = $this->conn->insert_id;

        // Завершить уровень
        $result = $this->questionService->completeLevel($userId, $levelId);
        $this->assertTrue($result);

        // Проверить прогресс
        $result = $this->conn->query("SELECT completed FROM progress WHERE user_id = $userId AND level_id = $levelId");
        $row = $result->fetch_assoc();
        $this->assertEquals(1, $row['completed']);
    }
}