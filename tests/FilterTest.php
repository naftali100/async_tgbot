<?php

declare(strict_types=1);

namespace bot_lib\Test;

use Amp\PHPUnit\AsyncTestCase;
use bot_lib\Filter;
use bot_lib\Test\UpdateTypes;

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
        $this->assertTrue($v($this->private_message));
        $this->assertFalse($v($this->cbq));

        $v = Filter::Message('text1');
        $this->assertFalse($v($this->private_message));

        $v = Filter::MessageRegex('/text/');
        $this->assertTrue($v($this->private_message));

        $v = Filter::MessageUpdates();
        $this->assertTrue($v($this->private_message));
        $this->assertFalse($v($this->cbq));
    }

    public function testCbqFilter()
    {
        $f = Filter::CbqUpdates();
        $this->assertTrue($f($this->cbq));
        $this->assertFalse($f($this->private_message));

        $f = Filter::Cbq(['dat1a', 'a']);
        $this->assertFalse($f($this->cbq));
        $f = Filter::Cbq(['data', 'a', 'b']);
        $this->assertTrue($f($this->cbq));
    }

    public function testFileFilter()
    {
        $f = Filter::fileUpdates();
        $this->assertTrue($f($this->photo_file));
        $this->assertFalse($f($this->private_message));

        $f = Filter::FileType('photo');
        $this->assertTrue($f($this->photo_file));
        $this->assertFalse($f($this->private_message));
    }

    public function testFilterJoinRequests()
    {
        $f = Filter::JoinRequests();
        $this->assertTrue($f($this->join_request));
        $this->assertFalse($f($this->private_message));
        $this->assertFalse($f($this->group_message));
    }

    public function testStartsWith()
    {
        $f = Filter::StartsWith('text', 'te');
        $this->assertTrue($f($this->private_message));
        $f = Filter::StartsWith('text', 're');
        $this->assertFalse($f($this->private_message));
    }

    public function testWebApp()
    {
        $this->assertNotNull($this->webapp_data->web_app_data);

        $f = Filter::webAppUpdates();
        $this->assertTrue($f($this->webapp_data));
        $this->assertFalse($f($this->private_message));

        $f = Filter::webData('data');
        $this->assertTrue($f($this->webapp_data));

        $f = Filter::webData('data1');
        $this->assertFalse($f($this->webapp_data));
    }

    public function testEditFilter(){
        $f = Filter::editUpdates();
        $this->assertTrue($f($this->edited_message));
        $this->assertFalse($f($this->private_message));
    }

    public function testServiceFilter(){
        $f = Filter::serviceUpdates();
        $this->assertTrue($f($this->pin_message));
        $this->assertTrue($f($this->new_member));
        $this->assertFalse($f($this->private_message));
    }

}