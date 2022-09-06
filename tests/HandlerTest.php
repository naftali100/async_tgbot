<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/basic_update.php';

use bot_lib\Update;
use bot_lib\Server;

use Amp\PHPUnit\AsyncTestCase;
use bot_lib\Config;

final class HandlerTest extends AsyncTestCase
{
    use UpdateTypes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->init();
    }

    // is every test end up using this, move this to setUp function
    public function setupServer()
    {
        $config = new Config();
        $config->load(__DIR__.'/conf.json');
        $this->server = new Server("127.0.0.1:1337");
        $this->server->load_handler('index', Closure::fromCallable([$this, 'handler']), $config);
        yield $this->server->run(false);
        $this->assertEquals(\Amp\Http\Server\HttpServer::STARTED, $this->server->getState());
    }

    public function testServerRequestPrivateMessage()
    {
        yield \Amp\call([$this,'setupServer']);

        yield $this->private_message->Request('http://127.0.0.1:1337/index', json_encode($this->private_message->update_arr))->plain;
    }

    protected function tearDownAsync()
    {
        if (isset($this->server)) {
            yield $this->server->stop();
        }
    }

    public function handler(Update $u)
    {
    }
}