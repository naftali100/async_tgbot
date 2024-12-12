<?php

namespace bot_lib;

class Loader
{
    public function __construct()
    {
        function camelToSnake($camelCase)
        {
            $pattern = '/(?<=\\w)(?=[A-Z])|(?<=[a-z])(?=\d)/';
            $snakeCase = preg_replace($pattern, '_', $camelCase);
            return strtolower($snakeCase);
        }

        spl_autoload_register(function ($class_name) {
            set_error_handler(function () { /* ignore errors */
            });
            include_once getcwd() . '/' . $class_name . '.php';
            include_once getcwd() . '/' . strtolower($class_name) . '.php';
            include_once getcwd() . '/' . camelToSnake($class_name) . '.php';
            include_once getcwd() . '/' . lcfirst(camelToSnake($class_name)) . '.php';

            include_once getcwd() . '/bots/' . $class_name . '.php';
            include_once getcwd() . '/bots/' . strtolower($class_name) . '.php';
            include_once getcwd() . '/bots/' . camelToSnake($class_name) . '.php';
            include_once getcwd() . '/bots/' . lcfirst(camelToSnake($class_name)) . '.php';
        });
        restore_error_handler();
    }
    public $bots = [];
    public function load($path, $botClass, $config)
    {
        $botInstance = new $botClass($config);
        if (!$botInstance instanceof Bot) {
            throw new \Error('invalid class '. get_class($botInstance) . '. all classes should extend the Bot abstract class');
        }
        $this->bots[$path] = ['class' => $botClass, 'config' => $config];
    }

    public function autoLoad($folder)
    {
        // auto load all classes in folder
    }
}
