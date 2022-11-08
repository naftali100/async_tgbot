<?php

declare(strict_types=1);

use Amp\PHPUnit\AsyncTestCase;
use bot_lib\Test\UpdateTypes;

final class HttpTest extends AsyncTestCase
{
    use UpdateTypes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->init();
    }

    public function testArrayBody(){
        $res = yield $this->private_message->Request('http://example.com', ['data' => 'data'])->promise;
        $this->assertEquals(200, $res->getStatus());
    }
}