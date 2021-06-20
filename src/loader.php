<?php

namespace bot_lib;

class Loader{
    public array $files = [];

    public function load_folder($path, $recursive = false){
        $path = rtrim($path, "/");
        $file_list = array_diff(scandir($path), array('.', '..')); 
        foreach($file_list as $file){
            if(is_file($path . "/". $file)){
                $this->load_file($path . "/".$file);
            }elseif(is_dir($path . "/" .$file) && $recursive){
                $this->load_folder($path . "/" . $file, $recursive);
            }
        }
    }

    /**
     * load handler from file
     * @param string $file_name the file name to open
     * @param string $as costom path to the file
     */
    public function load_file($file_name, $as = null){
        if(is_file($file_name)){
            list( $handler, $config) = self::include_file($file_name);
            if($as)
                $file_name = $as;
            $this->files[$file_name] = ["active" => 1, "handler" => $handler, "config" => $config];
        }else{
            print "file $file_name not fount" . PHP_EOL;
        }
    }

    public function load_handler($name, $handler, $config = null){
        if($config == null)
            $config = new Config;
        $this->files[$name] =  ["active" => 1, "handler" => $handler, "config" => $config];
    }

    // so the handler wont get the $this of the loader
    public static function include_file($name)
    {
        print "including $name " . PHP_EOL;
        $res = [];
        require $name;
        foreach (get_defined_vars() as $value) {
            if (is_a($value, "bot_lib\Handler")) {
                $res[0] = $value;
            }
            if (is_a($value, "bot_lib\Config")) {
                $res[1] = $value;
            }
        }
        if(!isset($res[0])){
            throw new \Error("can't find Handler instance");
        }
        if (!isset($res[1])) {
            $res[1] = new Config();
        }
        return $res;
    }
}
