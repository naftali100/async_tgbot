<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/basic_update.php';

use bot_lib\Update;
use bot_lib\Server;

use Amp\PHPUnit\AsyncTestCase;
use bot_lib\Config;
use bot_lib\Handler;

final class HandlerTest extends AsyncTestCase
{
    use UpdateTypes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->init();
    }

    // if every test end up using this, move this to setUp function
    public function setupServer($handlers = null)
    {
        return \Amp\call(function () use ($handlers) {
            $config = new Config();
            $config->renameLogger('handlersTest logger');
            // $config->setLevel('debug');
            $config->load(__DIR__ . '/conf.json');

            $this->server = new Server("127.0.0.1:1337");
            if ($handlers != null) {
                // handler is Handler
                $this->server->load_handler('index', $handlers, $config);
            } else {
                $this->server->load_handler('index', Closure::fromCallable([$this, 'handler']), $config);
            }

            yield $this->server->run(false);
            $this->assertEquals(\Amp\Http\Server\HttpServer::STARTED, $this->server->getState());
        });
    }

    public function handler(Update $u)
    {
        // empty handler
    }

    public function testServerRequestPrivateMessage()
    {
        // test with empty handler
        yield $this->setupServer();

        yield $this->private_message->Request('http://127.0.0.1:1337/index', json_encode($this->private_message->update_arr))->plain;
    }

    protected function tearDownAsync()
    {
        if (isset($this->server)) {
            yield $this->server->stop();
            $this->server->files = [];
        }
    }

    public function testOnMessage()
    {
        $handler = new Handler();
        $handler->on_message(
            filter: 'text',
            func: function ($u) {
                $this->assertEquals('text', $u->text);
            },
            name: 'testing on message'
        );

        $handler->on_message(
            filter: 'text1',
            func: function () {
                throw new Error('should not run');
            }
        );

        $handler->on_message(
            filter: fn ($u) => $u->text == 'text' && $u->chat->id == $this->user_id,
            func: function ($u) {
                $this->assertEquals('text', $u->text);
            }
        );

        $handler->on_message(
            filter: fn ($u) => $u->text == 'text1',
            func: function () {
                throw new Error('should not run');
            }
        );

        yield $this->setupServer($handler);

        yield $this->private_message->Request('http://127.0.0.1:1337/index', json_encode($this->private_message->update_arr))->plain;
    }

    public function testFreeName()
    {
        $handler = new Handler();
        $handler->free_name(
            filter: fn ($u) => $u->text == 'text1',
            func: function () {
                throw new Error('should not run');
            }
        );
        $handler->free_name(
            filter: fn ($u) => $u->text == 'text',
            func: function ($u) {
                $this->assertEquals('text', $u->text);
            }
        );

        yield $this->setupServer($handler);

        yield $this->private_message->Request('http://127.0.0.1:1337/index', json_encode($this->private_message->update_arr))->plain;
    }

    public function testOnCbq()
    {
        $handler = new Handler();
        $handler->on_cbq(
            filter: 'data',
            func: function ($u) {
                $this->assertEquals('text', $u->text);
                $this->assertEquals('data', $u->data);
            }
        );

        $handler->on_cbq(
            function ($u) {
                $this->assertEquals('text', $u->text);
            }
        );

        yield $this->setupServer($handler);

        yield $this->private_message->Request('http://127.0.0.1:1337/index', json_encode($this->cbq->update_arr))->plain;
    }

    public function testLast()
    {
        $handler = new Handler();

        $handler->on_message(
            function () {
                //
            },
            last: true
        );

        $handler->on_message(
            function () {
                throw new Error('should not run');
            }
        );

        yield $this->setupServer($handler);

        yield $this->private_message->Request('http://127.0.0.1:1337/index', json_encode($this->private_message->update_arr))->plain;
    }

    public function testNewChatMember()
    {
        $handler = new Handler();
        $handler->on_new_member(
            function ($u) {
                $this->assertEquals($this->user_id, $u->from->id);
            }
        );

        $handler->on_new_member(
            filter: fn ($u) => $u->chat->id == 01234,
            func: function ($u) {
                throw new Error('should not run');
            }
        );

        $handler->on_new_member(
            filter: fn ($u) => $u->chat->id == $this->chat_id,
            func: function () {
                // 
            }
        );
        $handler->on_message(
            function () {
                throw new Error('should not run');
            }
        );

        yield $this->setupServer($handler);

        yield $this->private_message->Request('http://127.0.0.1:1337/index', json_encode($this->new_member->update_arr))->plain;
    }
}
