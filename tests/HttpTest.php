<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/basic_update.php';

use Amp\PHPUnit\AsyncTestCase;

final class HttpTest extends AsyncTestCase
{
    use UpdateTypes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->init();
    }

    public function testArrayBody(){
        $res = $this->private_message->Request('http://example.com', ['data' => 'data']);
        // $res = $this->private_message->Request('http://example.com', ['data' => 'data'])->promise;
        $this->assertEquals(200, $res->getStatus());
    }
}