<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/basic_update.php';

use bot_lib\Update;

use Amp\PHPUnit\AsyncTestCase;

final class UpdateTest extends AsyncTestCase
{
    use UpdateTypes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->init();
    }

    public function testGetChatId(): void
    {
        $this->assertEquals($this->user_id, $this->private_message->chat->id);
        $this->assertEquals($this->user_id, $this->private_message['chat']['id']);

        $this->assertEquals($this->chat_id, $this->group_message->chat->id);
        $this->assertEquals($this->chat_id, $this->group_message['chat']['id']);

        $this->assertEquals($this->chat_id, $this->sender_chat->chat->id);
        $this->assertEquals($this->chat_id, $this->sender_chat['chat']['id']);

        $this->assertEquals($this->chat_id, $this->cbq->chat->id);
        $this->assertEquals($this->chat_id, $this->cbq['chat']['id']);
    }

    public function testGetFromChat()
    {
        $this->assertEquals($this->user_id, $this->private_message->from->id);
        $this->assertEquals($this->user_id, $this->private_message['from']['id']);

        $this->assertEquals($this->user_id, $this->group_message->from->id);
        $this->assertEquals($this->user_id, $this->group_message['from']['id']);

        $this->assertEquals($this->channel_id, $this->sender_chat->from->id);
        $this->assertEquals($this->channel_id, $this->sender_chat['from']['id']);

        $this->assertEquals($this->user_id, $this->cbq->from->id);
        $this->assertEquals($this->user_id, $this->cbq['from']['id']);

        $this->assertEquals($this->user_id, $this->inline_query->from->id);
        $this->assertEquals($this->user_id, $this->inline_query['from']['id']);

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

        $this->assertEquals($needle, $this->sender_chat->text);
        $this->assertEquals($needle, $this->sender_chat['text']);

        $this->assertEquals($needle, $this->cbq->text);
        $this->assertEquals($needle, $this->cbq['text']);

        $this->assertEquals($needle, $this->inline_query->text);
        $this->assertEquals($needle, $this->inline_query['text']);
    }

    public function testRequestResultType()
    {
        $promise = $this->private_message->sendMessage($this->myUserId, "hello");

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

    public function testChatType()
    {
        $this->assertEquals('private', $this->private_message->chatType);
        $this->assertEquals('supergroup', $this->group_message->chatType);
    }
}
