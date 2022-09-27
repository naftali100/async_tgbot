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
    }
}