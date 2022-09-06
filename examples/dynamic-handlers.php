<?php

/**
 * you can set what handlers will run by returning array of handlers from before
 */

use bot_lib\Config;
use bot_lib\Handler;
use bot_lib\TheHandler;

$conf = new Config();
$conf->load("conf.json");
$conf->server_url = "http://localhost:8081/bot";

$handler = new Handler;

$handler->before(function($u){
    // create new Handler to create the handlers (you can also return an array of TheHandler. see below)
    $new_handler = new Handler;
    $new_handler->on_update(fn($u) => $u->reply("yoo hoo"));

    return $new_handler->handlers;
});

// you can't set two before handlers, this is only an example
$handler->before(function ($u) {

    $new_handlers = [
        new TheHandler("on_message", "hello", fn($u) => $u->reply("yoo hoo"), true),
        new TheHandler("on_file", null, fn(bot_lib\Update $u) => $u->forward(-1000001000, true), true),
    ];

    return $new_handlers;
});