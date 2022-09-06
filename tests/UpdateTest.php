<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/basic_update.php';

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
    }

    protected function tearDownAsync()
    {
        if (isset($this->server)) {
            yield $this->server->stop();
        }
    }

    public function setUpServer()
    {
        $this->server = new Server("127.0.0.1:1337");
        $this->server->load_file(__DIR__ . "/handlers.php", "index");
        $this->server->run(false);
    }

    public function testGetChatId(): void
    {
        $this->assertEquals($this->chat_id, $this->private_message->chat->id);
        $this->assertEquals($this->chat_id, $this->private_message['chat']['id']);

        $this->assertEquals($this->chat_id, $this->group_message->chat->id);
        $this->assertEquals($this->chat_id, $this->group_message['chat']['id']);
    }

    public function testGetFromChat()
    {
        $this->assertEquals($this->user_id, $this->private_message->from->id);
        $this->assertEquals($this->user_id, $this->private_message['from']['id']);

        $this->assertEquals($this->user_id, $this->group_message->from->id);
        $this->assertEquals($this->user_id, $this->group_message['from']['id']);

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

    /**
     * @depend setUpServer
     */
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
