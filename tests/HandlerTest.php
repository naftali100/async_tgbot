<?php

declare(strict_types=1);

use Amp\PHPUnit\AsyncTestCase;
use bot_lib\Update;
use bot_lib\Server;
use bot_lib\Config;
use bot_lib\Handler;
use bot_lib\Filter;
use bot_lib\Test\UpdateTypes;

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
            // $config->debug = true;
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
            filter: Filter::Message('text'),
            func: function ($u) {
                $this->assertEquals('text', $u->text);
                $this->checkOnMessage2 = true;
            },
            name: 'testing on message'
        );

        $handler->on_message(
            filter: Filter::Message('text1'),
            func: function () {
                throw new Error('should not run');
            }
        );

        $handler->on_message(
            filter: [Filter::Message('text'), Filter::Chat($this->user_id)],
            func: function ($u) {
                $this->assertEquals('text', $u->text);
                $this->checkOnMessage1 = true;
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
        $this->assertTrue($this->checkOnMessage1);
        $this->assertTrue($this->checkOnMessage2);
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
            name: 'test only data filter',
            filter: Filter::Cbq('data'),
            func: function ($u) {
                $this->assertEquals('text', $u->text);
                $this->assertEquals('data', $u->data);

                $this->called1 = true;
            }
        );

        $handler->on_cbq(
            function ($u) {
                $this->assertEquals('text', $u->text);
                $this->assertEquals('data', $u->data);

                $this->called2 = true;
            },
            name: 'test only by function name'
        );

        $handler->on_cbq(
            filter: FIlter::Cbq('data1'),
            func: function () {
                throw new Error('should not run');
            },
            name: 'test wrong data'
        );

        yield $this->setupServer($handler);

        yield $this->private_message->Request('http://127.0.0.1:1337/index', json_encode($this->cbq->update_arr))->plain;
        $this->assertTrue($this->called1);
        $this->assertTrue($this->called2);
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

        $handler->on_service(
            function () {
                $this->testNewChatMemberCheck1 = true;
            }
        );

        yield $this->setupServer($handler);

        yield $this->private_message->Request('http://127.0.0.1:1337/index', json_encode($this->new_member->update_arr))->plain;

        $this->assertTrue($this->testNewChatMemberCheck1);
    }

    public function testOnService()
    {
        $handler = new Handler();

        $handler->on_service(
            function () {
                $this->testOnServiceCheck1 = true;
            }
        );

        yield $this->setupServer($handler);

        yield $this->private_message->Request('http://127.0.0.1:1337/index', json_encode($this->pin_message->update_arr))->plain;
        $this->assertTrue($this->testOnServiceCheck1);
    }

    public function testOnEdit()
    {
        $handler = new Handler();

        $handler->on_edit(
            function () {
                $this->testOnEditCheck1 = true;
            }
        );
        $handler->on_message(
            function () {
                throw new Error();
            }
        );
        $handler->on_service(
            function () {
                throw new Error();
            }
        );

        yield $this->setupServer($handler);

        yield $this->private_message->Request('http://127.0.0.1:1337/index', json_encode($this->edited_message->update_arr))->plain;
        $this->assertTrue($this->testOnEditCheck1);
    }

    public function testGroupMessage()
    {
        $handler = new Handler();

        $handler->on_message(
            name: 'group message - good',
            filter: function (Update $u) {
                return $u->chatType != 'private';
            },
            func: function () {
                $this->groupMessageCheck1 = true;
            }
        );

        $handler->on_message(
            name: 'group message - bad',
            filter: fn ($u) => $u->chatType == 'private',
            func: function () {
                throw new Error();
            }
        );


        yield $this->setupServer($handler);

        yield $this->private_message->Request('http://127.0.0.1:1337/index', json_encode($this->group_message->update_arr))->plain;
        $this->assertTrue($this->groupMessageCheck1);
    }

    public function testOnInline()
    {
        $handler = new Handler();

        $handler->on_inline(
            filter: fn (Update $u) => $u->chatType != 'private',
            func: function ($u) {
                $this->onInlineCheck1 = true;
            }
        );

        $handler->on_message(
            func: function ($u) {
                throw new Error();
            }
        );


        yield $this->setupServer($handler);

        yield $this->private_message->Request('http://127.0.0.1:1337/index', json_encode($this->inline_query->update_arr))->plain;
        $this->assertTrue($this->onInlineCheck1);
    }

    public function testHandlerWithFilter()
    {
        $handler = new Handler();

        $handler->on_message(
            filter: Filter::Message('text'),
            func: function () {
                $this->HandlerWithFilterCheck1 = true;
            }
        );
        $handler->on_update(
            filter: Filter::Message('text'),
            func: function () {
                $this->HandlerWithFilterCheck2 = true;
            }
        );
        $handler->checkThis(
            filter: Filter::Message('text'),
            func: function () {
                $this->HandlerWithFilterCheck3 = true;
            }
        );

        $this->HandlerWithFilterCheck4 = false;
        $handler->on_cbq(
            filter: Filter::Cbq('text'),
            func: function () {
                $this->HandlerWithFilterCheck4 = true;
            }
        );

        yield $this->setupServer($handler);

        yield $this->private_message->Request('http://127.0.0.1:1337/index', json_encode($this->private_message->update_arr))->plain;
        $this->assertTrue($this->HandlerWithFilterCheck1);
        $this->assertTrue($this->HandlerWithFilterCheck2);
        $this->assertTrue($this->HandlerWithFilterCheck3);
        $this->assertFalse($this->HandlerWithFilterCheck4);
    }
}
