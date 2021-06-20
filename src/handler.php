<?php

namespace bot_lib;

/**
 * the actual handler
 *  
 * store the func and the condition if passed
 * 
 * @param string $when name of handler. can be used as a filter.
 * @param string|array|Closure $filter whether or not run the func. can be string or array combined with "when" param or func that take Update as paramter and return bool
 * @param Closure $func  the function that will run if all condition met
 * @param bool $last  whether or not keep runing handlers or this should be the last
 */
class TheHandler{

    public $active = true;

    function __construct(private $when, private $filter, private $func, public $last){
        if(gettype($this->filter) == "string"){
            $this->filter = [$this->filter];
        }
    }

    function runHandler($update, ...$args){
        return \Amp\call($this->func, $update, ...$args);
    }

    public function runMiddle($update, $hanler){
        $h = function($update, ...$args)use($hanler){
            return $hanler->run_handler($update, ...$args);
        };
        return \Amp\call($this->func, $update, $h);
    }

    /**
     * determinate whether or not the handler should run
     * @param Update update - the update
     * 
     * @return bool
     */
    public function shouldRun(Update $update): bool{
        if(is_callable($this->filter) ){
            return call_user_func($this->filter, $update);
        }else{
            switch($this->when){
                case "on_update": 
                    if(empty($this->filter) || in_array($update->updateType, $this->filter))
                        return true;
                    else
                        return false;
                break;
                case "on_message":
                    if($update->updateType == "message" && (empty($this->filter) || in_array($update->message, $this->filter)))
                        return true;
                    else
                        return false;
                break;
                case "on_cbq":
                    if($update->updateType == "callback_query" && (empty($this->filter) || in_array($update->data, $this->filter)))
                        return true;
                    else
                        return false;
                break;
                case "on_file":
                    if(isset($update->media["file_type"]) && (empty($this->filter) || in_array($update->media["file_type"], $this->filter)))
                        return true;
                    else 
                        return false;
                break;
                case "on_service":
                    return $update->service_message;
                break;
                default:
                    return true;
            }
        }
    }

    public function next($func, $filter = [], $when = "", $last = false){
        $this->backup = [$this->when, $this->func, $this->filter, $this->last];
        $this->func = $func;
        if (gettype($filter) == "string")
            $this->filter = [$filter];
        else
            $this->filter = $filter;
        $this->last = $last;
        $this->when = $when;
    }

    public function back(){
        $this->when = $this->backup[0];
        $this->func = $this->backup[1];
        $this->filter = $this->backup[2];
        $this->last = $this->backup[3];
    }
}

/**
 * per file
 */
class Handler{
    public TheHandler $before;
    public TheHandler $fallback;
    public TheHandler $middle;
    public TheHandler $after;
    public TheHandler $on_error; 
    public array $handlers = [];

    public function __construct( Update|null $update = null)
    {
        if($update != null)
            $this->update = $update;
    }

    function __call($func_name, $args)
    {
        try {
            $func = $args["func"] ?? $args[0] ?? null;
            if ($func == null) {
                print "handler $func didn't have func set. ignoring" . PHP_EOL;
                return;
            }
            $filter = $args["filter"] ?? $args[1] ?? [];
            $last = $args["last"] ?? $args[2] ?? false;

            if(in_array($func_name, ["fallback", "middle", "before", "after", "on_error"])){
                $this->$func_name = new TheHandler($func_name, $filter, $func, $last);
            }else{
                $this->handlers[] = new TheHandler($func_name, $filter, $func, $last);
            }
        } catch (\Throwable $e) {
            print $e->getMessage();
        }
    }
}
