<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/basic_update.php';

use Amp\PHPUnit\AsyncTestCase;

use bot_lib\Filter;

final class FilterTest extends AsyncTestCase
{
    use UpdateTypes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->init();
    }

    public function testMessageFilters(){
        $v = Filter::Message('text');
        $this->assertTrue($v->validate($this->private_message));
        $this->assertFalse($v->validate($this->cbq));
        $v = Filter::Message('text1');
        $this->assertFalse($v->validate($this->private_message));
        $v = Filter::MessageRegex('/text/');
        $this->assertTrue($v->validate($this->private_message));
        $v = Filter::MessageUpdates();
        $this->assertTrue($v->validate($this->private_message));
        $this->assertFalse($v->validate($this->cbq));
    }

    public function testCbqFilter(){
        $f = Filter::CbqUpdates();
        $this->assertTrue($f->validate($this->cbq));
        $f = Filter::Cbq(['dat1a', 'a']);
        $this->assertFalse($f->validate($this->cbq));
        $f = Filter::Cbq(['data', 'a', 'b']);
        $this->assertTrue($f->validate($this->cbq));
    }

    public function testFileTypeFilter(){
        $f = Filter::FileType('photo');
        $this->assertTrue($f($this->photo_file));
        $this->assertFalse($f($this->private_message));
    }

    public function testFilterJoinRequests(){
        $f = Filter::JoinRequests();
        $this->assertTrue($f($this->join_request));
        $this->assertFalse($f($this->private_message));
        $this->assertFalse($f($this->group_message));
    }
}