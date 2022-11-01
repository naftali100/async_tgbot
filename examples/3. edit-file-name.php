<?php

use bot_lib\Config;
use bot_lib\Handler;
use bot_lib\Update;

$conf = new Config();
$conf->load('conf.json');

$handler = new Handler;

$handler->on_file(function(Update $u){
    $file = yield $u->download()->array; // get decoded json response
    try{
        yield Amp\File\move($file["result"]["file_path"], "new_name");
        $fn = "send" . $u->media["file_type"]; // get the right function to send the file
        $u->$fn($u->chat->id, "new_name", caption: "your renamed file");
    }catch(Throwable $e){
        $u->reply("failed to rename");
    }
});