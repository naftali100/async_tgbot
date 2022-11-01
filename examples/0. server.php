<?php

// example of server capable to run multiple bots
require_once __DIR__."/../vendor/autoload.php";

use bot_lib\Server;

$listen = "127.0.0.1:1337";
$server = new Server($listen);

$server->load_file("bot.php");
$server->load_file("bot2.php", "index"); // will run the handlers in bot2.php on requset to "127.0.0.1:1234/index" instead of "127.0.0.1:1234/bot2.php"
$server->load_file("manager.php", "index.php");
$server->load_folder("folder_full_of_bots");

// if you want to use cluster you have to run the server with 
// $ vendor/bin/cluster server.php
$server->run();