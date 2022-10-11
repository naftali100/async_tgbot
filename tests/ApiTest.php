<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/basic_update.php';

use Amp\PHPUnit\AsyncTestCase;

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
        $res = $this->private_message->sendPhoto($this->myUserId, __DIR__ . '/image.jpg')->update;
        $this->assertTrue($res->ok);
    }

    public function testGetMessage()
    {
        $res = $this->private_message->getMessage(227774988, 1)->array;
        $this->assertTrue($res['ok']);
    }

    public function testReplyWithEntFromUpdate()
    {
        $res = $this->private_message->sendMessage($this->myUserId, 'text', entities: $this->private_with_ent->ent)->update;
        $this->assertTrue($res->ok);
    }

    // public function testReplyWithKeyboardFromUpdate()
    // {
    //     $res = $this->private_message->sendMessage($this->myUserId, 'hello', $this->cbq->keyboard)->getUpdate();
    //     var_dump($res->update);
    //     var_dump($res->request_info);
    //     $this->assertTrue($res->ok);
    // }
}
