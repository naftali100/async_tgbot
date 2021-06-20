<?php
/**
 * the server that running all bots
 */

namespace bot_lib;

use Amp\ByteStream;
use Amp\Cluster\Cluster;
use Amp\Promise;
use Amp\ByteStream\ResourceOutputStream;
use Amp\ByteStream\ResourceInputStream;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Socket;
use Monolog\Logger;
use Amp\Loop;

use bot_lib\Loader;

class Server extends Loader {
    // public array $files = []; - set in Loader

    public function __construct($servers = null)
    {
        $this->servers = $servers;
    }

    private function prepareServers(bool $cluster = false){
        if (gettype($this->servers) == "array") {
            $listening_servers = array_map(function ($elem)use($cluster) {
                if($cluster) return Cluster::listen($elem);
                return Socket\Server::listen($elem);
            }, $this->servers);
        } elseif (gettype($this->servers) == "string") {
            if($cluster) $listening_servers = [Cluster::listen($this->servers)];
            else $listening_servers = [Socket\Server::listen($this->servers)];
        } else {
            if($cluster) $listening_servers = [Cluster::listen("127.0.0.1:1337")];
            else $listening_servers = [Socket\Server::listen("127.0.0.1:1337")];
        }
        return $listening_servers;
    }

    public function run(){
        Loop::run(function () { 
            try{
                $servers = $this->prepareServers();

                $logHandler = new StreamHandler(new ResourceOutputStream(STDOUT));
                $logHandler->setFormatter(new ConsoleFormatter);
                $logger = new Logger('bots server');
                $logger->pushHandler($logHandler);
    
                $server = new HttpServer($servers, new CallableRequestHandler(function (Request $request) use ($logger) {
                    try{
                        \Amp\Promise\rethrow(\Amp\call([$this, "requestHandler"], $request, $logger));
                    }catch(\Throwable $e){
                        print $e->getMessage() . " when handleing request to " . $request->getUri() . " on " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
                    }
                    return new Response(Status::OK, [
                        "content-type" => "text/plain; charset=utf-8"
                    ], "ok");
                }), $logger);
    
                yield $server->start();
    
                Loop::onSignal(\SIGINT, static function (string $watcherId) use ($server) {
                    Loop::cancel($watcherId);
                    yield $server->stop();
                    exit();
                });
    
                // cli-options
                // get info about running bots from console
                $in = new ResourceInputStream(STDIN);
    
                while (($chunk = yield $in->read()) !== null) {
                    $flag = false;
                    switch (trim($chunk)) {
                        case "ls":
                            foreach ($this->files as $file_name => $h) {
                                print $file_name . " active: " . $h["active"] . "\n";
                            }
                            break;
                        case "ll":
                            foreach ($this->files as $file_name => $h) {
                                print $file_name . PHP_EOL;
                                foreach ($h["handler"] as $key => $hh) {
                                    if ($key == "handlers") {
                                        continue;
                                    }
                                    print $key . PHP_EOL;
                                }
                                foreach ($h["handler"]->handlers as $h) {
                                    print var_export($h) . PHP_EOL;
                                }
                            }
                            break;
                        case "reload":
                            // TODO: how to reload the files too
                            print "restarting server..." . PHP_EOL;
                            yield $server->stop(2000);
                            Loop::stop();
                            $flag = true;
                            break;
                        case "exit":
                            exit();
                            break;
                        default:
                            print "can't understand you. available options are: exit/reload/ls/ll/^C" . PHP_EOL;
                    }
                    if ($flag) break;
                }
            }catch(\Throwable $e){
                print $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine() . PHP_EOL;
                yield $server->stop();
                Loop::stop();
            }
        });
    }

    public function runCluster(){
        Loop::run(function () { 
            try{
                $servers = yield $this->prepareServers(true);

                // Creating a log handler in this way allows the script to be run in a cluster or standalone.
                if (Cluster::isWorker()) {
                    $handler = Cluster::createLogHandler();
                } else {
                    $handler = new StreamHandler(ByteStream\getStdout());
                    $handler->setFormatter(new ConsoleFormatter);
                }

                $logger = new Logger('worker-' . Cluster::getId());
                $logger->pushHandler($handler);

                // Set up a simple request handler.
                $server = new HttpServer($servers, new CallableRequestHandler(function (Request $request) use ($logger) {
                    try {
                        \Amp\Promise\rethrow(\Amp\call([$this, "requestHandler"], $request, $logger));
                    } catch (\Throwable $e) {
                        print $e->getMessage() . " when handleing request to " . $request->getUri() . " on " . $e->getFile().":".$e->getLine() . PHP_EOL;
                    }
                    return new Response(Status::OK, [
                        "content-type" => "text/plain; charset=utf-8"
                    ], "ok");
                }), $logger);

                // Start the HTTP server
                yield $server->start();

                // Stop the server when the worker is terminated.
                Cluster::onTerminate(function () use ($server): Promise {
                    return $server->stop();
                });
                
            }catch(\Throwable $e){
                print $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine() . PHP_EOL;
                yield $server->stop();
                Loop::stop();
            }
        });
    }

    /**
     * get the path of request
     * load the update and file's handler using his config
     * then run the handlers
     */
    public function requestHandler($request, $logger){
        $path = ltrim($request->getUri()->getPath(), "/");
        if (isset($this->files[$path])) {
            if ($this->files[$path]["active"]) {
                // debug
                $this->files[$path]["config"]->debug && $logger->info("running file: " . $path);

                try {
                    $time = \Amp\Loop::now();

                    $file = $this->files[$path];
                   
                    $update_string = yield $request->getBody()->buffer();
                    $update = new Update($file["config"], $update_string);

                    $run_info = [$file["config"], $file["handler"], $update];
                    
                    parse_str($request->getUri()->getQuery(), $query);
                    if (isset($query["token"]))
                        $run_info[0]->token = $query["token"];

                    $res = yield \Amp\call([$this, "runHandler"], $run_info);
                    $file["config"]->debug && $logger->info("took: ".\Amp\Loop::now() - $time.". handlers result", $res ?? []);

                } catch (\Throwable $e) {
                    $logger->error("error '" . $e->getMessage() . "' in file " . $e->getFile() . " in line " . $e->getLine() . ". path " . $path . " - disabled!");
                    $this->files[$path]["active"] = 0;
                }
            }
        } else {
            $logger->notice("file " . $path . " not exist");
        }
    }

    public function runHandler($data){
        list($config, $handler, $update) = $data;

        if ($config->token === null)
            throw new \Error("token not set");

        if ($update === null || $update->update === null)
            throw new \Error("update not set");

        $called = false;
        $promises = [];

        $handlers_to_run = $handler->handlers; 

        if (isset($handler->before)) {
            $new_handlers = yield $handler->before->runHandler($update, $config->async);
            if (gettype($new_handlers) == "array") {
                $handlers_to_run = $new_handlers;
            }
        }

        foreach ($handlers_to_run as $theHandler) {
            if ($theHandler->shouldRun($update)) {
                if (isset($handler->middle)) {
                    $promises[] = $handler->middle->runMiddle($update, $theHandler);
                } else {
                    $promises[] = $theHandler->runHandler($update);
                }
                $called = true;
                if ($theHandler->last)
                    break;
            }
        }
        if (!$called && isset($handler->fallback)) {
            if (isset($handler->middle)) {
                $promises[] = $handler->middle->runMiddle($update, $handler->fallback);
            } else {
                $promises[] = $handler->fallback->runHandler($update);
            }
        }
        
        try {
            $res = yield $promises;
            if (isset($handler->after)) {
                $res[] = yield $handler->after->runHandler($update, $config->async);
            }
            return $res;
        } catch (\Throwable $e) {
            print $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine() . PHP_EOL;
            if (isset($this->on_error)) {
                return $handler->on_error->runHandler($e, $config->async) ?? [];
            }
        }
    }
}
