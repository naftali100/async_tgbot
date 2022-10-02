<?php

namespace bot_lib;

/**
 * load bots files.
 * 
 * you shouldn't use this class directly. use Server. see examples
 */
class Loader
{
    /**
     * contain all file content 
     * 
     * array of: path => BotFile
     */
    public array $files = [];

    public function load_folder($path, $recursive = false)
    {
        $path = rtrim($path, '/');
        $file_list = array_diff(scandir($path), array('.', '..'));
        foreach ($file_list as $file) {
            if (is_file($path . '/' . $file)) {
                $this->load_file($path . '/' . $file);
            } elseif (is_dir($path . '/' . $file) && $recursive) {
                $this->load_folder($path . '/' . $file, $recursive);
            }
        }
    }

    /**
     * load handler from file
     * @param string $file_name the file name to open
     * @param string $as custom path to the file
     */
    public function load_file($file_name, $as = null)
    {
        if (is_file($file_name)) {
            list($handler, $config) = $this->include_file($file_name);

            $path = $file_name;
            if ($as) {
                $path = $as;
            }

            $this->files[$path] = new BotFile($file_name, true, $handler, $config);
        } else {
            print 'file ' . $file_name . ' not found' . PHP_EOL;
        }
    }

    public function load_handler($name, $handler, $config = null)
    {
        if ($config == null) {
            $config = new Config;
        }
        // if function - create new Handler
        if (get_class($handler) == 'Closure') {
            $handler_obj = new Handler();
            $handler_obj->func($handler);
            $handler = $handler_obj;
        }
        $this->files[$name] = new BotFile('', true, $handler, $config);
    }

    private function include_file($name)
    {
        $res = [];

        require $name;
        foreach (get_defined_vars() as $value) {
            if (is_a($value, 'bot_lib\Handler')) {
                $res[0] = $value;
            }
            if (is_a($value, 'bot_lib\Config')) {
                $res[1] = $value;
            }
        }

        if (!isset($res[0]))
            throw new \Error('can\'t find Handler instance');

        if (!isset($res[1]))
            $res[1] = new Config();

        $res[2] = Update::class;

        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, Update::class)) {
                $res[2] = $class;
                break;
            }
        }

        return $res;
    }
}
