<?php

// example of server capable to run multiple bots
require_once __DIR__ . "/../vendor/autoload.php";

use bot_lib\Config;
use bot_lib\Server;
use bot_lib\Loader;

$loader = new Loader();

$loader->load('EchoBot', EchoBot::class, Config::fromJsonFile(__DIR__ . '/conf.json')); // bot class is auto loaded

$server = new Server($loader);

// if you want to use cluster you have to run the server with
// $ vendor/bin/cluster server.php
$server->run();
