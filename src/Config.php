<?php

namespace bot_lib;

class Config
{
    public function __construct(public $token = '', public $baseUrl = 'https://api.telegram.org/bot')
    {
    }

    public static function fromJsonFile($path)
    {
        if (\Amp\File\exists($path)) {
            $file = \Amp\File\read($path);
            return new Config(
                ...json_decode($file, true)
            );
        }
        return new Config();
    }

    public static function fromEnvFile($path)
    {
        if (\Amp\File\exists($path)) {
            $env = \Amp\File\read($path);
            $lines = explode("\n", $env);
            $config = [];

            foreach ($lines as $line) {
                // read env file. ignoring comments
                preg_match("/([^#]+)\=(.*)/", $line, $matches);
                if (isset($matches[2])) {
                    $config[trim($matches[1])] = trim($matches[2]);
                }
            }
            return new Config(
                ...$config
            );
        }
        return new Config();
    }
}
