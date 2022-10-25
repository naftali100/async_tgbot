<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/basic_update.php';

use Amp\PHPUnit\AsyncTestCase;
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
        $res = yield $this->private_message->getMessage(227774988, 1)->array;
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
            "text1" => [
                "web_app" => [
                    'url' => 'https://nbots.ga:1338'
                ]
            ]
        ]], false);
        $res = yield $this->private_message->sendMessage($this->myUserId, 'text', $key);
        print_r($key);
        print_r($res->update);
    }
}
