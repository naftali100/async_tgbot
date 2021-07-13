<?php

// example of server capable to run mutiple bots
require_once "../src/bot_lib.php";
require_once "../vendor/autoload.php";

use bot_lib\Server;

$listen = "127.0.0.1:1337";
$server = new Server($listen);

$server->load_file("bot.php");
$server->load_file("bot2.php", "index"); // will run the handlers in bot2.php on requset to "127.0.0.1:1234/index" instead of "127.0.0.1:1234/bot2.php"
$server->load_file("manager.php", "index.php", true); // this file is load with extra access. 
$server->load_folder("folder_full_of_bots");

// you can choose whether run the server with one thread or multiple. pass 'true' param to run method to run cluster
// if you use cluster you have to run the server with vendor/bin/cluster server.php 
$server->run();