<?php

use bot_lib\Config;
use bot_lib\Handler;

$conf = new Config("123456789:qwertyui");

$handler = new Handler;

$handler->on_update(function($u){
    $u->reply($u->text);
});
