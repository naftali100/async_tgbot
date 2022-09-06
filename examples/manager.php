<?php

use bot_lib\Config;
use bot_lib\Handler;
use bot_lib\TheHandler;

$conf = new Config();
$conf->load("config.json");
$conf->server_url = "http://localhost:8081/bot";

$handler = new Handler();

$handler->before(
    function($u){
        if($u->chatId != 22777498) return []; // restrict access to this id only
    }
);

$handler->on_update(fn($u) => $u->reply("asdf"));

$handler->reload_file(
    filter: fn($u) => str_starts_with($u->text, "reload "),
    func: function($u){
        $words = explode(" ", $u->text);
        if(isset($this->files[$words[1]])){
            unset($words[0]);
            $this->load_file(...$words);
        }else{
            $u->reply("file not exist");
        }
    }
);

$handler->toggle_file(
    filter: fn($u) => str_starts_with($u->text, "toggle "),
    func: function($u){
        $words = explode(" ", $u->text);
        if (isset($this->files[$words[1]])) {
            $this->files[$words[1]]["active"] != $this->files[$words[1]]["active"];
        } else {
            $u->reply("file not exist");
        }
    }
);

$handler->reload_all_files(
    filter: fn ($u) => $u->text == "reload",
    func: function($u){
        foreach($this->files as $path => $file){
            if(!str_ends_with(__FILE__, $file["file_name"])){ // don't reload me
                $file_name = $file["file_name"];
                unset($file);
                $this->load_file($file_name, $path);
            }
        }
    }
);
