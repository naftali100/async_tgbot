<?php

use bot_lib\Config;
use bot_lib\HandlersHub;

class BotFile
{
    function __construct(public $fileName, public bool $active, public HandlersHub $handler, public Config $config, public $update_class_name)
    {
    }
}