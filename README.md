# bot_lib

simple flexible and async library based on amphp for telegram bot api.

## installation

- download and extract bot_lib repo.
- run `composer update` to install amphp.

## getting started

the recommended way is to use amphp's server to run all your bots

#### server.php

```php
require_once("src/bot_lib.php");
use bot_lib\Server; 

$server = new Server("127.0.0.1:8080"); // create server instance listening to port 8080
$server->load_file("bot.php", "index"); // load the handlers in "bot.php" and store them in "index" path
$server->load_folder("folder", true); // load all files in a folder. the second param is whether to load recursively or not
$server->run();
```
#### bot.php

```php
use bot_lib\Config;
use bot_lib\HandlerHub;

$config = new Config;
$config->load("conf.json"); // can store token
$config->server = "http://loadlhost:8081/bot"; // if you using local telegram-bot-api

$handler = new HandlerHub;
$handler->on_message(
    fn($u) => $u->reply("hello");
);
```
set webhook to 127.0.0.1:8080/you_bot_file_name (or custom name passed in second argument to load_file).

you can add `token` parameter to the webhook url and the server will set it and use this token.

run `php server.php`.

a lot more handler, config and server options in examples folder.

---
# explanation

there is 4 main objects in the library

1. Server: extends Loader. load files, running the http-server and activating handlers.
2. Config: configuration.
3. Update: extends API and HTTP. contains all the method to send request to bot api.
4. Handler: create handlers that will run asynchronously.

### Server 
the server is loading all of your robots files, take the handlers, and run a server listen to incoming requests.
once there is a request to the server, it activates the handlers set in the request path. you can set any request path to any file.

### Handler
all handlers run asynchronously on every request from the bot. 
there is a verity of handlers you can set and ways to control how they will activate.

to create handler, simply call the method on Handler instance as handler name you want `$handler->handler_name()`.

you can give it any name you want (accept the handler class methods).
the name can control when the handler is activating.

handler accepts 3 parameter

- function (named func): the function to run when handler activated. accept Update instance as parameter.
- filter: string, array or function that determine whether the handler should run or not. accept Update instance as parameter.
- last: if true and handler is activated, the handler will be the last handler to run in current request.

you can pass the parameters by order (function, filter, last) or by name `$handler->on_message(filter: "blabal", func: fn($u) => $u->reply("blablabl"));`

#### special handlers names
this list of handler can accept also string or array filters. any other handler should filter with function

- on_update: activates on every update. accepts update type/s as filter (message, callback_query, etc).
- on_message: activates on 'message' updates. accept message/s as filter (/start, menu, word, test, etc).
- on_cbq: activates on 'callback_query' updates. accept text to match callback_data as filter.
- on_file: activates when there is file in the request (no matter what update type). accept file type/s as filter (photo, audio, etc).

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

You can load file, folders or HandlersHub.

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
