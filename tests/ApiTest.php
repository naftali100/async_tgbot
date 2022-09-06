<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/basic_update.php';

use Amp\PHPUnit\AsyncTestCase;

final class ApiTest extends AsyncTestCase
{
    use UpdateTypes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->init();
    }

    public function testFileUpload(){
        $res = yield $this->private_message->sendPhoto($this->myUserId, __DIR__. '/image.jpg')->array;
        $this->assertTrue($res['ok']);
    }
}