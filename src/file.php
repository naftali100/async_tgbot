<?php

use bot_lib\Config;
use bot_lib\Handler;

class BotFile
{
    function __construct(public $fileName, public bool $active, public Handler $handler, public Config $config)
    {
    }
}