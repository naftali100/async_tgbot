<?php
namespace bot_lib;

class BotFile
{
    function __construct(public $fileName, public bool $active, public Handler $handler, public Config $config)
    {
    }
}