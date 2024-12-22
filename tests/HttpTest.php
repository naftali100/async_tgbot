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

    public function _testArrayBody()
    {
        $res = $this->config->http->Request('http://example.com', ['data' => 'data']);
        $this->assertEquals(200, $res->getStatus());
    }

    public function testTest()
    {
        $this->assertTrue(true);
    }
}
