<?php

use bot_lib\Config;
use bot_lib\HandlersHub;

$conf = new Config("123456789:qwertyui");

$handler = new HandlersHub();

$handler->on_update(function($u){
    $u->reply($u->text);
});
