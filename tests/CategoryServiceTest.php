<?php

declare(strict_types=1);

namespace Worldus\Tests;

use PHPUnit\Framework\TestCase;
use Worldus\CategoryService;
use Worldus\DatabaseInterface;

class CategoryServiceTest extends TestCase
{
    public function testGetCategoriesPageReturnsPaginationData(): void
    {
        $countResult = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['fetch_assoc'])
            ->getMock();
        $countResult->method('fetch_assoc')->willReturn(['total' => 2]);

        $categoryResult = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['fetch_assoc'])
            ->getMock();
        $categoryResult->expects($this->exactly(3))
            ->method('fetch_assoc')
            ->willReturnOnConsecutiveCalls(
                ['id' => 1, 'name' => 'Test 1', 'levels_count' => 1],
                ['id' => 2, 'name' => 'Test 2', 'levels_count' => 3],
                null
            );

        $db = $this->createMock(DatabaseInterface::class);
        $db->expects($this->exactly(2))
            ->method('query')
            ->willReturnOnConsecutiveCalls($countResult, $categoryResult);

        $service = new CategoryService($db);
        $result = $service->getCategoriesPage(1);

        $this->assertSame(1, $result['page']);
        $this->assertSame(2, $result['total']);
        $this->assertSame(1, $result['total_pages']);
        $this->assertCount(2, $result['categories']);
    }

    public function testDeleteCategoryCascadeExecutesAllQueries(): void
    {
        $db = $this->createMock(DatabaseInterface::class);
        $db->expects($this->exactly(5))
            ->method('query')
            ->willReturn(true);

        $service = new CategoryService($db);
        $this->assertTrue($service->deleteCategoryCascade(42));
    }
}
