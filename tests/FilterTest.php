<?php

declare(strict_types=1);

namespace bot_lib\Test;

use Amp\PHPUnit\AsyncTestCase;
use bot_lib\Filter;
use bot_lib\Test\UpdateTypes;
use Respect\Validation\Validator as v;

final class FilterTest extends AsyncTestCase
{
    use UpdateTypes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->init();
    }

    public function testMessageFilters()
    {
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

    public function testCbqFilter()
    {
        $f = Filter::CbqUpdates();
        $this->assertTrue($f->validate($this->cbq));
        $this->assertFalse($f->validate($this->private_message));

        $f = Filter::Cbq(['dat1a', 'a']);
        $this->assertFalse($f->validate($this->cbq));
        $f = Filter::Cbq(['data', 'a', 'b']);
        $this->assertTrue($f->validate($this->cbq));
    }

    public function testFileFilter()
    {
        $f = Filter::fileUpdates();
        $this->assertTrue($f->validate($this->photo_file));
        $this->assertFalse($f->validate($this->private_message));

        $f = Filter::FileType('photo');
        // $this->assertTrue($f->validate($this->photo_file));
        $this->assertFalse($f->validate($this->private_message));
    }

    public function testFilterJoinRequests()
    {
        $f = Filter::JoinRequests();
        $this->assertTrue($f->validate($this->join_request));
        $this->assertFalse($f->validate($this->private_message));
        $this->assertFalse($f->validate($this->group_message));
    }

    public function testStartsWith()
    {
        $f = Filter::StartsWith('text', 'te');
        $this->assertTrue($f->validate($this->private_message));
        $f = Filter::StartsWith('text', 're');
        $this->assertFalse($f->validate($this->private_message));
    }

    public function testWebApp()
    {
        $this->assertNotNull($this->webapp_data->web_app_data);

        $f = Filter::webAppUpdates();
        $this->assertTrue($f->validate($this->webapp_data));
        $this->assertFalse($f->validate($this->private_message));

        $f = Filter::webData('data');
        $this->assertTrue($f->validate($this->webapp_data));

        $f = Filter::webData('data1');
        $this->assertFalse($f->validate($this->webapp_data));
    }

    public function testEditFilter()
    {
        $f = Filter::editUpdates();
        $this->assertTrue($f->validate($this->edited_message));
        $this->assertFalse($f->validate($this->private_message));
    }

    public function testServiceFilter()
    {
        $f = Filter::serviceUpdates();
        $this->assertTrue($f->validate($this->pin_message));
        $this->assertTrue($f->validate($this->new_member));
        $this->assertFalse($f->validate($this->private_message));
    }

    public function testNewMemberFilter()
    {
        $f = Filter::newMember();
        $this->assertTrue($f->validate($this->new_member));
        $this->assertFalse($f->validate($this->pin_message));
        $this->assertFalse($f->validate($this->private_message));
    }

    public function testChannelMessage()
    {
        $f1 = v::allOf(
            Filter::messageUpdates(),
            Filter::message('text'),
            Filter::chatType('channel'),
            Filter::channelChat()
        );
        // $this->assertTrue($f1->validate($this->channel_message));

        $f2 = v::NoneOf(
            Filter::privateChat(),
            Filter::cbqUpdates(),
            Filter::editUpdates(),
            Filter::inlineUpdates(),
            Filter::serviceUpdates()
        );
        $this->assertTrue($f2->validate($this->channel_message));
    }
}
