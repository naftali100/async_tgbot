<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/basic_update.php';

use Amp\PHPUnit\AsyncTestCase;
use bot_lib\Response;

final class HttpTest extends AsyncTestCase
{
    use UpdateTypes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->init();
    }

    public function testRequestResultType()
    {
        $response = $this->private_message->sendMessage($this->myUserId, "hello");

        $res = $response;
        $this->assertIsObject($res);
        $this->assertInstanceOf(Response::class, $res);

        $res = $response->result;
        $this->assertIsString($res);

        $res = $response->json;
        $this->assertIsString($res);

        $res = $response->array;
        $this->assertIsArray($res);

        $res = $response->decode;
        $this->assertIsArray($res);
    }

    public function testArrayBody(){
        $res = $this->private_message->Request('http://example.com', ['data' => 'data']);
        $this->assertEquals(200, $res->getStatus());
    }
}