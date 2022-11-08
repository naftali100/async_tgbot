<?php
declare(strict_types=1);

namespace bot_lib\Test;
// TODO: auto load or something...
require_once __DIR__.'/UpdateTypes.php';

use Amp\PHPUnit\AsyncTestCase;
use bot_lib\Test\UpdateTypes;
use bot_lib\Helpers;

final class ApiTest extends AsyncTestCase
{
    use UpdateTypes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->init();
    }

    public function testPhotoUpload()
    {
        $res = yield $this->private_message->sendPhoto($this->myUserId, __DIR__ . '/image.jpg');
        $this->assertTrue($res->ok);
    }

    public function testGetMessage()
    {
        $res = yield $this->private_message->getMessage($this->myUserId, 1)->array;
        $this->assertTrue($res['ok']);
    }

    public function testReplyWithEntFromUpdate()
    {
        $res = yield $this->private_message->sendMessage($this->myUserId, 'text', entities: $this->private_with_ent->ent);
        $this->assertTrue($res->ok);
    }

    public function testKeyboard()
    {
        $key = Helpers::keyboard([[
            "text" => [
                "web_app" => [
                    'url' => 'https://google.com'
                ]
            ]
        ]], false);
        $res = yield $this->private_message->sendMessage($this->myUserId, 'text', $key);
        $this->assertTrue($res->ok);
    }
}
