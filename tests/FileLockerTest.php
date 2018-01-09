<?php

use Jinraynor1\Utils\FileLocker;

class FileLockerTest extends PHPUnit_Framework_TestCase
{
    public function testCanLockFile()
    {
        $lock_name = 'lock-test';
        $lock_dir = __DIR__ . '/locks';

        $locker = new FileLocker($lock_name, $lock_dir);
        $locker->lock();
        $this->assertTrue($locker->isLocked());

        $locker2 = new FileLocker($lock_name, $lock_dir);
        $this->assertTrue($locker2->isLocked());

        $locker->unlock();
        $this->assertFalse($locker2->isLocked());


    }
}