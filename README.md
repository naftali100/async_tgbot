# bot_lib

simple flexible and async library based on amphp for telegram bot api.

## installation

- run ```composer require naftali100/async_bot```.

## getting started

#### server.php

```php
require_once './vendor/autoload.php';

use bot_lib\Server; 

$server = new Server("127.0.0.1:8080"); // create server instance listening to port 8080
$server->load_file("bot.php"); // load the handlers in "bot.php" and store them in "bot.php" path
$server->load_file("bot1.php", "index"); // you can add second param for different path
$server->load_folder("folder", true); // load all files in a folder. the second param is whether to load recursively or not
$server->run();
```
#### bot.php

```php
require_once './vendor/autoload.php';

use bot_lib\Config;
use bot_lib\Handler;
use bot_lib\Update;
use bot_lib\Filter;

$config = new Config;
$config->load("conf.json"); // can store token
$config->server_url = "http://loadlhost:8081/bot"; // if you using local telegram-bot-api

$handler = new Handler;
$handler->on_message(
    fn(Update $u) => $u->reply("hello"),
    Filter::Message('/start')
);
```
set webhook to 127.0.0.1:8080/you_bot_file_name.php (or custom name passed in second argument to load_file).

you can add `token` parameter to the webhook url and the server will set it and use this token.

run `php server.php`.

a lot more handler, config and server options in examples folder.

---
# explanation

there is 5 main objects in the library

1. Server: extends Loader. load files, running the http-server and activating handlers.
2. Config: configuration.
3. Update: extends API and HTTP. contains all the method to send request to bot api.
4. Handler: create handlers that will run asynchronously.
5. Filter: static methods to create filters.

### Server 
the server is loading all of your robots files, take the handlers, and run a server listen to incoming requests.
once there is a request to the server, it activates the handlers set in the request path. you can set any request path to any file.

### Handler
all handlers run asynchronously on every request from the bot. 
there is a verity of handlers you can set and ways to control how they will activate.

to create handler, simply call the method on Handler instance as handler name you want `$handler->handler_name()`.

you can give it any name you want (except the handler class methods).
the name can control when the handler is activating.

handler accepts 4 parameter

- function (named func): the function to run when handler activated. accept Update instance as parameter.
- filter: must be callable. if you passed a function, it should receive one argument - Update instance
- last: if true and handler is activated, the handler will be the last handler to run in current request.
- name: name of the handler, useful for debugging what handler is activated.

you can pass the arguments by order (function, filter, last, name) or by name
```php
$handler->on_message(
    filter: "blabal", 
    func: fn($u) => $u->reply("bla"),
    last: true,
    name: 'reply bla to blabla'
);
```

#### special handlers names
##### this list of special handler activated in specific TBD.
- before: activated before any other handler. can return new array of function to run instead of existing handlers, useful for disabling all of them by returning empty array.
- middle: run before every handler. accept 2 arguments: Update and $next witch us the function of the handler.
- after: activates after all handlers finished. useful for cleaning, or writing to db.
- fallback: activates only if no other handler was activated.

##### this list of special handler activated in specific update types.

- on_update: activates on every update. accepts update type/s as filter (message, callback_query, etc).
- on_message: activates on 'message' updates. accept message/s as filter (/start, menu, word, test, etc).
- on_edit: activates on 'edited_message' updates. accept new message/s as filter.
- on_cbq: activates on 'callback_query' updates. accept text to match callback_data as filter.
- on_file: activates when there is file in the request (no matter what update type). accept file type/s as filter (photo, audio, etc).
- on_service: activated when update is service message, do not accept string or array filter, only function (string or array will result the handler not activating).

### Config
you can config various things see src/config.php file. can be set in json file and load using `load` method as shown above.

### Update
All bot api method and some more in this class. instance of this class is passed to the handlers.

telegram api methods - https://core.telegram.org/bots/api#available-methods

#### Added methods:

- reply: reply to the message.
- delete: delete the message.
- ban: [only in groups and channels] ban the user from chat.
- leave: leave group or channel.
- edit: edit the message.
- forward: forward the message to another chat with credit or as copy.
- alert: reply to callback_query.
- download: download media in message.
- editKeyboard: edit inline keyboard.
- editButton: edit only one inline button.

Also there is a lot of preset variables to many update parts. see update.php file.

#### variables 
Partial list:

- chat: the chat where the message sent.
- from: the user that send the message. same as chat in private message.
- updateType: the update type (message, callback_query, etc).
- cb_answered: whether callback_query answered or not. can be used in after handler to answer if not answered.
- service: if message is service message.
- media: contain media from message.

you can access the update as object or as array. `$u->message->chat->id` or `$u['message']['chat']['id']`.

you can skip the update type (message in above example). 

`$u->data` will be the callback data in callback update. `$u->message_id` is the message_id.

### Loader
The Server class extends the Loader. you shouldn't use it directly.

You can load file, folders or Handler.

##### extra access
File loaded with the ability to change handlers of other bots.

The handlers in file loaded with $this of bot_lib/Server.

The `files` prop in Server class contain the

- path 
    - file_name 
    - handler 
    - config 
 
of every file loaded by the server.

Examples what you can do with extra access in examples folder.

### Helper
contain static helpers functions.

- keyboard: easily create inline keyboard. see comment how to use.
- permissions: create [ChatPermissions](https://core.telegram.org/bots/api#chatpermissions) json.
