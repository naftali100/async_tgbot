<?php

use bot_lib\Config;
use bot_lib\Handler;
use bot_lib\Update;
use bot_lib\Filter;

$conf = new Config();
$conf->load("conf.json");

$handler = new Handler();

$handler->welcome(
    filter: Filter::message('/start'),
    func: function(Update $u){
        $u->reply('welcome to the bot');
    }
);