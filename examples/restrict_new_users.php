<?php

use bot_lib\Config;
use bot_lib\HandlersHub;
use bot_lib\Helpers;

$conf = new Config;
$conf->load('conf.json');

$handler = new HandlersHub;

$handler->on_mew_member(
    func: function(bot_lib\Update $u){
        $u->restrictChatMember($u->chat->id, $u->from->id);
        $message = yield $u->reply('please press', Helpers::keyboard([['I am human' => $u->from->id]]));
        yield Amp\delay(1000 * 60);
        $message->delete(); // delete the message and ban the user if the user does not press within 1 minute. 
        $u->ban();
    }
);

$handler->on_cbq(
    function(bot_lib\Update $u){
        if($u->from->id == $u->data){
            $u->restrictChatMember($u->chat->id, $u->from->id, json_encode((yield $u->getChat($u->chat->id))['permissions']));
            $u->alert('you are a human!');
        }else{
            $u->alert('you can\'t verify others');
        }
    }
);