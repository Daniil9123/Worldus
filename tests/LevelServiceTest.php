<?php

declare(strict_types=1);

namespace Worldus\Tests;

use PHPUnit\Framework\TestCase;
use Worldus\DatabaseInterface;
use Worldus\LevelService;

class LevelServiceTest extends TestCase
{
    public function testIsLevelLockedReturnsTrueWhenPreviousLevelNotCompleted(): void
    {
        $levelResult = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['fetch_assoc'])
            ->getMock();
        $levelResult->method('fetch_assoc')->willReturn(['id' => 2, 'required_level' => 1]);

        $previousResult = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['fetch_assoc'])
            ->getMock();
        $previousResult->method('fetch_assoc')->willReturn(['id' => 1]);
        $previousResult->num_rows = 1;

        $progressResult = new class {
            public int $num_rows = 0;
        };

        $prepareStmt = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['bind_param', 'execute', 'get_result'])
            ->getMock();
        $prepareStmt->method('bind_param')->willReturn(true);
        $prepareStmt->method('execute')->willReturn(true);
        $prepareStmt->method('get_result')->willReturn($levelResult);

        $db = $this->createMock(DatabaseInterface::class);
        $db->method('prepare')->willReturn($prepareStmt);
        $queries = [];
        $db->method('query')->willReturnCallback(function ($sql) use (&$queries, $previousResult, $progressResult) {
            $queries[] = $sql;

            if (strpos($sql, 'SELECT id FROM levels WHERE category_id = 1 AND level_order < 2 ORDER BY level_order DESC LIMIT 1') !== false) {
                return $previousResult;
            }

            if (strpos($sql, 'SELECT id FROM progress WHERE user_id = 1 AND level_id = 1 AND completed = 1 LIMIT 1') !== false) {
                return $progressResult;
            }

            return null;
        });

        $service = new LevelService($db);
        $this->assertTrue($service->isLevelLocked(2, 1));
        $this->assertCount(1, $queries);
    }
}
