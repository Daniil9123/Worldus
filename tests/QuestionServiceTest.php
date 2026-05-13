<?php

declare(strict_types=1);

namespace Worldus\Tests;

use PHPUnit\Framework\TestCase;
use Worldus\DatabaseInterface;
use Worldus\QuestionService;

class QuestionServiceTest extends TestCase
{
    public function testValidateLevelCategoryReturnsFalseWhenMissing(): void
    {
        $resultRow = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['fetch_assoc'])
            ->getMock();
        $resultRow->num_rows = 0;
        $resultRow->method('fetch_assoc')->willReturn(null);

        $stmt = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['bind_param', 'execute', 'get_result'])
            ->getMock();
        $stmt->method('bind_param')->willReturn(true);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($resultRow);

        $db = $this->createMock(DatabaseInterface::class);
        $db->method('prepare')->willReturn($stmt);

        $service = new QuestionService($db);

        $this->assertFalse($service->validateLevelCategory(1, 1));
    }

    public function testGetQuestionCountReturnsNumber(): void
    {
        $result = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['fetch_assoc'])
            ->getMock();
        $result->method('fetch_assoc')->willReturn(['total' => 5]);

        $db = $this->createMock(DatabaseInterface::class);
        $db->method('query')->willReturn($result);

        $service = new QuestionService($db);

        $this->assertSame(5, $service->getQuestionCount(10));
    }
}
