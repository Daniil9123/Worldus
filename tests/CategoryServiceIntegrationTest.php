<?php

declare(strict_types=1);

namespace Worldus\Tests;

use Worldus\CategoryService;

/**
 * @group integration
 */
class CategoryServiceIntegrationTest extends IntegrationTestCase
{
    private CategoryService $categoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryService = new CategoryService($this->db);
    }

    public function testAddCategoryAndGetCategoriesPage(): void
    {
        // Добавить категорию
        $categoryId = $this->categoryService->addCategory('Test Category', 'test.jpg', 5);
        $this->assertIsInt($categoryId);

        // Проверить получение страницы категорий
        $pageData = $this->categoryService->getCategoriesPage(1);
        $this->assertIsArray($pageData);
        $this->assertArrayHasKey('categories', $pageData);
        $this->assertArrayHasKey('total_pages', $pageData);
        $this->assertCount(1, $pageData['categories']);
        $this->assertEquals('Test Category', $pageData['categories'][0]['name']);
        $this->assertEquals(5, $pageData['categories'][0]['levels_count']);
    }

    public function testDeleteCategoryCascade(): void
    {
        // Добавить категорию с уровнем
        $categoryId = $this->categoryService->addCategory('Test Category', 'test.jpg', 1);

        // Проверить, что уровень существует
        $result = $this->conn->query("SELECT COUNT(*) as count FROM levels WHERE category_id = $categoryId");
        $count = $result->fetch_assoc()['count'];
        $this->assertEquals(1, $count);

        // Удалить категорию каскадно
        $result = $this->categoryService->deleteCategoryCascade($categoryId);
        $this->assertTrue($result);

        // Проверить, что категория и уровни удалены
        $result = $this->conn->query("SELECT COUNT(*) as count FROM categories WHERE id = $categoryId");
        $count = $result->fetch_assoc()['count'];
        $this->assertEquals(0, $count);

        $result = $this->conn->query("SELECT COUNT(*) as count FROM levels WHERE category_id = $categoryId");
        $count = $result->fetch_assoc()['count'];
        $this->assertEquals(0, $count);
    }
}