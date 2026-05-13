<?php

declare(strict_types=1);

namespace Worldus\Tests;

use Worldus\LevelService;

/**
 * @group integration
 */
class LevelServiceIntegrationTest extends IntegrationTestCase
{
    private LevelService $levelService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->levelService = new LevelService($this->db);
    }

    public function testIsLevelLocked(): void
    {
        // Создать пользователя, категорию, уровни
        $this->conn->query("INSERT INTO users (email, password, role) VALUES ('testuser@example.com', 'hash', 'user')");
        $userId = $this->conn->insert_id;
        $this->conn->query("INSERT INTO categories (name, image, description) VALUES ('Test Cat', 'test.jpg', '2 уровней')");
        $categoryId = $this->conn->insert_id;
        $this->conn->query("INSERT INTO levels (category_id, title, level_order, required_level) VALUES ($categoryId, 'Level 1', 1, NULL)");
        $level1Id = $this->conn->insert_id;
        $this->conn->query("INSERT INTO levels (category_id, title, level_order, required_level) VALUES ($categoryId, 'Level 2', 2, $level1Id)");
        $level2Id = $this->conn->insert_id;

        // Level 2 должен быть заблокирован, так как Level 1 не пройден
        $locked = $this->levelService->isLevelLocked($level2Id, $userId);
        $this->assertTrue($locked);

        // Завершить Level 1
        $this->conn->query("INSERT INTO progress (user_id, level_id, completed) VALUES ($userId, $level1Id, 1)");

        // Теперь Level 2 не заблокирован
        $locked = $this->levelService->isLevelLocked($level2Id, $userId);
        $this->assertFalse($locked);
    }
}