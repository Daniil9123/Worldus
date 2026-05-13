<?php

declare(strict_types=1);

namespace Worldus\Tests;

use PHPUnit\Framework\TestCase;
use Worldus\DatabaseInterface;
use Worldus\LevelService;

class LevelServiceTest extends TestCase
{
    public function testIsLevelLockedReturnsTrueWhenRequiredNotCompleted(): void
    {
        $levelResult = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['fetch_assoc'])
            ->getMock();
        $levelResult->method('fetch_assoc')->willReturn(['id' => 2, 'required_level' => 1]);

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
        $db->expects($this->once())
            ->method('query')
            ->with('SELECT id FROM progress WHERE user_id = 1 AND level_id = 1 AND completed = 1 LIMIT 1')
            ->willReturn($progressResult);

        $service = new LevelService($db);
        $this->assertTrue($service->isLevelLocked(2, 1));
    }
}
