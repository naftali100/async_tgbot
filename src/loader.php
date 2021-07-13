<?php

namespace bot_lib;


/**
 * load bots files.
 * 
 * you shouldn't use this class directly. use Server. see examples
 */
class Loader{
    /**
     * cntain all file content 
     * 
     * - path
     *      -- file_name
     *      -- active
     *      -- HandlerHub
     *      -- config
     */
    public array $files = [];

    public function load_folder($path, $recursive = false){
        $path = rtrim($path, '/');
        $file_list = array_diff(scandir($path), array('.', '..')); 
        foreach($file_list as $file){
            if(is_file($path . '/'. $file)){
                $this->load_file($path . '/'.$file);
            }elseif(is_dir($path . '/' .$file) && $recursive){
                $this->load_folder($path . '/' . $file, $recursive);
            }
        }
    }

    /**
     * load handler from file
     * @param string $file_name the file name to open
     * @param string $as costom path to the file
     * @param bool $extraAccess load the file with access to Server scop
     */
    public function load_file($file_name, $as = null, $extraAccess = false){
        if(is_file($file_name)){
            if($extraAccess)  list($handler, $config) = $this->include_file_this($file_name);
            else              list($handler, $config) = self::include_file_static($file_name);
            
            $path = $file_name;
            if($as)
                $path = $as;

            $this->files[$path] = ['file_name' => $file_name, 'active' => 1, 'handler' => $handler, 'config' => $config];
        }else{
            print 'file '. $file_name .' not fount' . PHP_EOL;
        }
    }

    public function load_handler($name, $handler, $config = null){
        if($config == null)
            $config = new Config;
        $this->files[$name] =  ['active' => 1, 'handler' => $handler, 'config' => $config];
    }

    public function include_file_this($name){
        print "including $name with extra access" . PHP_EOL;

        $res = [];

        require $name;
        foreach (get_defined_vars() as $value) {
            if (is_a($value, 'bot_lib\HandlersHub')) {
                $res[0] = $value;
            }
            if (is_a($value, 'bot_lib\Config')) {
                $res[1] = $value;
            }
        }

        if (!isset($res[0])) 
            throw new \Error('can\'t find HandlersHub instance');

        if (!isset($res[1])) 
            $res[1] = new Config();

        return $res;
    }
        
    // so the handler wont get the $this of the loader
    public static function include_file_static($name)
    {
        print "including $name " . PHP_EOL;
        $res = [];
        require $name;
        foreach (get_defined_vars() as $value) {
            if (is_a($value, 'bot_lib\HandlersHub')) {
                $res[0] = $value;
            }
            if (is_a($value, 'bot_lib\Config')) {
                $res[1] = $value;
            }
        }
        if(!isset($res[0])){
            throw new \Error('can\'t find HandlersHub instance');
        }
        if (!isset($res[1])) {
            $res[1] = new Config();
        }
        return $res;
    }
}
