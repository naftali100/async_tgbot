# bot_lib

simple flexiable and async library based on amphp for telegram bot api.

## installation

- download and extract bot_lib repo.
- run `composer update` to install amphp.

## getting started

the recomended way is to use amphp's server to run all your bots

#### server.php

```php
$server = new Server("127.0.0.1:8080"); // create server instance running on port 8080
$server->load_file("bot.php", "index"); // load the handlers in "bot.php" and store them in "index" path
$server->load_folder("folder", true); // load all files in a folder. the second param is whether to load recursivly or not
$server->run();
```
#### bot.php

```php
$config = new Config;
$config->load("conf.json"); // can store token
$config->server = "http://loadlhost:8081/bot"; // if you using local telegram-bot-api

$handler = new Handler;
$hanlder->on_message(
    fn($u) => $u->reply("hello");
);
```
set webhook to 127.0.0.1:8080/you_bot_file_name (or custom name passed in seacond argument to load_file).

you can add `token` paramter to the webhook url and the server will set it and use this token.

run `php server.php`.

a lot more hanlder, config and server options in examples folder.

---
# explanation

there is 4 main objects in the library

1. Server: extends Loader. load files and runing the http-server and activating handlers.
2. Config: configuration.
3. Update: extends API and HTTP. contains all the method to send request to bot api.
4. Handler: create handlers that will run asyncronicly.

### Server 
the server is loading all of your robots files, take the handlers, and run a server listen to incoming requests.
once there is a request to the server, it activates the handlers set in the request path. you can set any request path to any file.

### Handler
all handlers run asynchronicly on every request from the bot. 
there is a verity of handlers you can set and ways to control how they will activate.

to create handler, simply call the method on Handler instance as handler name you want `$handler->handler_name()`.

you can give it any name you want (accept the handler class methods).
the name can control when the handler is activating.

handler accepts 3 paramter

- function (named func): the function to run when handler activated. accept Update instance as paramter.
- filter: string, array or function that determine whether the handler should run or not. accept Update instance as paramter.
- last: if true and handler is activated, the handler will be the last handler to run in corrent request.

you can pass the parametrs by order (function, filter, last) or by name `$handler->on_message(filter: "blabal", func: fn($u) => $u->reply("blablabl"));`

#### special handlers names
this list of handler can accept also string or array filters. any other handler should filter with function

- on_upadte: activates on every update. accepts update type/s as filter (message, callback_query, etc).
- on_message: activates on 'message' updates. accept message/s as filter (/start, menu, word, test, etc).
- on_cbq: activates on 'callback_quesy' updates. accept text to match callback_data as filter.
- on_file: activates when there is file in the request (no metter whet update type). accept file type/s as filter (photo, audio, etc).

### Config
you can config varios things see src/config.php file. can be set in json file and load using `load` method as shown above.

### Update
all bot api method and some more in this class. instance of this class is passed to the handlers.

telegram api methods - https://core.telegram.org/bots/api#available-methods

added methods:

- reply: reply to the incoming message.
- delete: delete the message.
- ban: [only in groups and channels] ban the user from chat.
- leave: leave group or channel.
- edit: edit the message.
- forward: forward the message to another chat with or as copy.
- alert: reply to callbacl_quesy this alert.
- editKeyboard: edit inline keyboard.
- editButton: edit only one inline button.

Also there is a lot of preset variables to many update parts. see update.php file.