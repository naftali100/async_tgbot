<?php

// example of server capable to run multiple bots
require_once __DIR__ . "/../vendor/autoload.php";

use bot_lib\Config;
use bot_lib\Server;

$server = new Server();
$server->load('EchoBot-http-path', EchoBot::class, Config::fromJsonFile(__DIR__ . '/conf.json')); // bot class is auto loaded

// if you want to use cluster you have to run the server with
// $ vendor/bin/cluster server.php
$server->setWebhooks();
$server->run();
