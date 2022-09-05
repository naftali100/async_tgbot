<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/basic_update.php';
require_once __DIR__ . '/../src/bot_lib.php';

use bot_lib\Update;
use bot_lib\Server;

use Amp\PHPUnit\AsyncTestCase;

final class UpdateTest extends AsyncTestCase
{
    use UpdateTypes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->init();

        $server = new Server("127.0.0.1:1337");
        $server->load_file(__DIR__ . "/handlers.php", "index");
        $server->run(false);
        $this->server = $server;
    }

    protected function tearDown(): void
    {
        $this->server->stop();
    }

    public function testGetPrivateChatId(): void
    {
        $this->assertEquals($this->chat_id, $this->private_message->chat->id);
        $this->assertEquals($this->chat_id, $this->private_message['chat']['id']);
    }

    public function testForwardedChatId(): void
    {
        $this->assertEquals($this->user_id, $this->forwarded_message->from->id);
    }

    public function testGetText(): void
    {
        $needle = 'text';
        $this->assertEquals($needle, $this->private_message->text);
        $this->assertEquals($needle, $this->private_message['text']);
        $this->assertEquals($needle, $this->private_message->message->text);

        $this->assertEquals($needle, $this->forwarded_message->text);
        $this->assertEquals($needle, $this->forwarded_message['text']);
        $this->assertEquals($needle, $this->forwarded_message->message->text);

        $this->assertEquals($needle, $this->edited_message->text);
        $this->assertEquals($needle, $this->edited_message['text']);

        $this->assertEquals($needle, $this->group_message->text);
        $this->assertEquals($needle, $this->group_message['text']);
    }

    public function testRequest()
    {
        $promise = $this->private_message->sendMessage(227774988, "hello");

        $res = yield $promise;
        $this->assertIsObject($res);
        $this->assertInstanceOf(Update::class, $res);

        $res = yield $promise->result;
        $this->assertIsString($res);

        $res = yield $promise->json;
        $this->assertIsString($res);

        $res = yield $promise->array;
        $this->assertIsArray($res);

        $res = yield $promise->decode;
        $this->assertIsArray($res);
    }
}
