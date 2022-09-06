<?php

namespace bot_lib;

/**
 * per file
 */
class Handler extends HandlersCreator
{
    public TheHandler $before;
    public TheHandler $fallback;
    public TheHandler $middle;
    public TheHandler $after;
    public TheHandler $on_error;
    public array $handlers = []; // TODO only public because of separation.. wrong... 

    public function __construct(Update|null $update = null)
    {
        if ($update != null)
            $this->update = $update;
    }

    public function activate($config, $update)
    {
        if ($config->token === null) {
            throw new \Error('token not set');
        }

        if ($update === null || $update->update === null) {
            throw new \Error('update not set');
        }

        $called = false;
        $promises = [];

        $handlers_to_run = $this->handlers;
        // run before handler
        if (isset($this->before)) {
            $new_handlers = yield $this->before->runHandler($update, $config->async);
            if (gettype($new_handlers) == 'array') {
                $handlers_to_run = $new_handlers;
            }
        }
        // run handlers
        foreach ($handlers_to_run as $theHandler) {
            if ($theHandler->shouldRun($update)) {
                if (isset($this->middle)) {
                    $promises[] = $this->middle->runMiddle($update, $theHandler);
                } else {
                    $promises[] = $theHandler->runHandler($update);
                }
                $called = true;
                if ($theHandler->last) {
                    break;
                }
            }
        }
        // run fallback handler
        if (!$called && isset($this->fallback)) {
            if (isset($this->middle)) {
                $promises[] = $this->middle->runMiddle($update, $this->fallback);
            } else {
                $promises[] = $this->fallback->runHandler($update);
            }
        }
        // wait for handler to finish and run after handler
        $res = [];
        try {
            $res = yield $promises;
            if (isset($this->after)) {
                $res[] = yield $this->after->runHandler($update, $config->async);
            }
        } catch (\Throwable $e) {
            // TODO: get backtrace to the file where the error coming from
            print $e->getMessage() . ', when running handlers in ' . $e->getFile() . ' line ' . $e->getLine() . PHP_EOL;
            if (isset($this->on_error)) {
                $res[] = yield $this->on_error->runHandler($e, $config->async);
            }
        }
        return $res;
    }
}

/**
 * the actual handler
 *  
 * store the func and the condition if passed
 * 
 * @param string $when name of handler. can be used as a filter.
 * @param string|array|Closure $filter whether or not run the func. can be string or array combined with 'when' param or func that take Update as parameter and return bool
 * @param Closure $func  the function that will run if all condition met
 * @param bool $last  whether or not keep running handlers or this should be the last
 */
class TheHandler
{

    public $active = true;

    private array|\Closure $filter;

    function __construct(public $when, $filter, private $func, public $last)
    {
        if (gettype($filter) == 'string') {
            $this->filter = [$filter];
        } else {
            $this->filter = $filter;
        }
    }

    function runHandler($update, ...$args)
    {
        return \Amp\call($this->func, $update, ...$args);
    }

    public function runMiddle($update, $handler)
    {
        $h = function ($update, ...$args) use ($handler) {
            return $handler->run_handler($update, ...$args);
        };
        return \Amp\call($this->func, $update, $h);
    }

    /**
     * determinate whether or not the handler should run
     * @param Update update - the update
     * 
     * @return bool
     */
    public function shouldRun(Update $update): bool
    {
        $shouldRun = true;
        switch ($this->when) {
            case 'on_update':
                $shouldRun = $this->checkFilter($this->filter, $update->updateType, $update);
                break;
            case 'on_message':
                $shouldRun = $update->updateType == 'message' &&  $this->checkFilter($this->filter, $update->text, $update);
                break;
            case 'on_cbq':
                $shouldRun = $update->updateType == 'callback_query' && $this->checkFilter($this->filter, $update->data, $update);
                break;
            case 'on_file':
                $shouldRun = isset($update->media['file_type']) && $this->checkFilter($this->filter, $update->media['file_type'], $update);
                break;
            case 'on_service':
                $shouldRun = $update->service;
                break;
            case 'on_member':
                $shouldRun = $update->new_chat_members != null || $update->left_chat_member != null || in_array($update->updateType, ['chat_member', 'my_chat_member']);
                break;
            case 'on_new_member':
                $shouldRun = $update->new_chat_members != null;
                break;
            default:
                $shouldRun = true;
        }


        return $shouldRun;
    }

    private function checkFilter($filter, $data, $update)
    {
        if (empty($filter)) {
            return true;
        }
        if (is_callable($filter)) {
            return call_user_func($filter, $update);
        }
        // TODO: add regex support
        return in_array($data, $filter);
    }

    public function next($func, $filter = [], $when = '', $last = false)
    {
        $this->backup = [$this->when, $this->func, $this->filter, $this->last];
        $this->func = $func;
        if (gettype($filter) == 'string')
            $this->filter = [$filter];
        else
            $this->filter = $filter;
        $this->last = $last;
        $this->when = $when;
    }

    public function back()
    {
        $this->when = $this->backup[0];
        $this->func = $this->backup[1];
        $this->filter = $this->backup[2];
        $this->last = $this->backup[3];
    }
}


class HandlersCreator
{
    function __call($func_name, $args)
    {
        try {
            $func = $args['func'] ?? $args[0] ?? null;
            if ($func == null || !is_callable($func)) {
                print "handler $func didn't have func set. ignoring" . PHP_EOL;
                return;
            }
            $filter = $args['filter'] ?? $args[1] ?? [];
            $last = $args['last'] ?? $args[2] ?? false;

            if (in_array($func_name, ['fallback', 'middle', 'before', 'after', 'on_error'])) {
                $this->$func_name = new TheHandler($func_name, $filter, $func, $last);
            } else {
                $this->handlers[] = new TheHandler($func_name, $filter, $func, $last);
            }
        } catch (\Throwable $e) {
            print $e->getMessage();
        }
    }

    public function __invoke($func, $filter = [], $last = false)
    {
        $this->__call('anonymous', [$func, $filter, $last]);
    }
}