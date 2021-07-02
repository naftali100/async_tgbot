<?php

/**
 * basic usage examples
 */
   
use bot_lib\Config;
use bot_lib\HandlersHub;
use bot_lib\Update;
use bot_lib\Helpers;

 
$conf = new Config();
$conf->load("conf.json");
$conf->server = "http://localhost:8081/bot";
$conf->debug = true;
$conf->apiErrorHandler = function($err, $res){
    if($err){
        print "request error" . PHP_EOL;
    }
};

$handler = new HandlersHub();

// this handler run once per request before any other handler
$handler->before(function($u) {
    if(user_in_black_list($u->chatId))
        return []; // you can disable all handlers by returning empty array
    
    $u->blabla = "some data"; // will be available to all handlers
    $u->user = new User($u->chatId); // say you have class that load user from db, and you wand to use it with all handlers
});

$handler->middle(function($u, $next){
    // if you have somthing to run before any handler
    // unlike before, middle will run once per handler

    // will done before handler
    yield $next($u); // wait the handler to finish
    // will done  after handler
});

$handler->after(function($u){
    // run after all handlers finished
    store_in_db($u->user);
});

// run everytime on avery update type
// you can filter update type you want this handler to hanlde
$handler->on_update(function(Update $u){
    $u->reply($u->message);
}); 

$handler->on_start(
    filter: function($u){
        return str_starts_with($u->message, "/start");
    },
    func: function($u){
        $u->reply("you send \"/start\"");
    },
    last: true // if this handler is runnin it will be the last. and no other handlers will run
);

$handler->hey_i_can_call_handlers_whatever_i_want(function($u){
    $u->reply("haha cool handler name");
});

$handler->on_message( // only few handlers can take array or string as filter. 
    filter: ["***", "xxx" , "f"],
    func: fn($u) => $u->reply("hey! you can't sey stuff like this in here") // arrow function
);

$handler->on_file(function($u){
    $file = yield $u->download()->decode;
    $u->reply("your file downloaded to: " . $file["result"]["file_path"]);
}, ["audio", "photo"]);
// here we pass args by order. 
// if you wand to write the filter first, you need to use named arguments like on_start handler in this example

$handler->send_keyboard(
    filter: fn($u) => $u->message == "keyboard",
    func: fn($u) => $u->reply(
        "message with keyboard", 
        Helpers::keyboard([["option one" => "one"], ["row two" => "two"]])
    )
);

$handler->on_cbq( // another handler that accept string|array filter
    filter: "keyboard",
    func: function($u){
        $u->alert("you press the button " . $u->data);
    }
);

// handler without filter will run every request
$handler->everytime(function($u){
    $u->reply("-----");
});

$handler->do_some_request(function($u){
    $decoded_result = yield $u->Request("http://exapmle.com/json")->decode;
    $plain_text_result = yield $u->Request("http://exapmle.com/json")->result;

    // if the result is big you can read is asynchronicly
    $file = yield Amp\File\open("big_file", "w"); // open file
    $response = yield $u->Request("http://exapmle.com/big_file")->request; // wait for response
    while(null !== $chunk = yield $response->getBody()->read()){ // read response
        yield $file->write($chunk); // write it to the file
    }
});