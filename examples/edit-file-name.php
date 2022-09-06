<?php

use bot_lib\Config;
use bot_lib\Handler;

$conf = new Config("123456789:qwertyui");

$handler = new Handler;

$handler->on_file(function($u){
    $file = yield $u->download()->decode; // get decoded json response
    try{
        yield Amp\File\rename($file["result"]["file_path"], "new_name");
        $fn = "send" . $u->media["file_type"]; // get the right function to send the file
        $u->$fn($u->chatId, "new_name", caption: "your renamed file");
    }catch(Throwable $e){
        $u->reply("failed to rename");
    }
});