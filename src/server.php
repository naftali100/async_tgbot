<?php
/**
 * the server that running all bots
 */

namespace bot_lib;

use Amp\ByteStream;
use Amp\Cluster\Cluster;
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

    /** http server instance */
    private $server; 

    public function __construct($servers = null)
    {
        $this->servers = $servers;
    }

    public function run($as_cluster = false){ // TODO: replace with auto determiantion by command (php or bin/cluster)
        Loop::run(function () use($as_cluster) { 
            try{
                // TODO: fix this
                $servers = $as_cluster ? (yield $this->prepareServers($as_cluster)) : $this->prepareServers($as_cluster);

                $logger = $this->get_logger($as_cluster);

                // Set up a request handler.
                $server = new HttpServer($servers, new CallableRequestHandler(function (Request $request) use ($logger) {
                    try {
                        \Amp\asyncCall([$this, 'requestHandler'], $request, $logger);
                    } catch (\Throwable $e) {
                        print $e->getMessage() . ' when handleing request to ' . $request->getUri() . ' on ' . $e->getFile().':'.$e->getLine() . PHP_EOL;
                    }
                    return new Response(Status::OK, [
                        'content-type' => 'text/plain; charset=utf-8'
                    ], 'ok');
                }), $logger);

                // Start the HTTP server
                yield $server->start();
                $this->server = $server;

                \Amp\call(\Closure::fromCallable([$this, 'cli_options']));

                if($as_cluster){
                    // Stop the server when the worker is terminated.
                    Cluster::onTerminate(function () use ($server) {
                        return $server->stop();
                    });
                }else{
                    Loop::onSignal(\SIGINT, static function (string $watcherId) use ($server) {
                        Loop::cancel($watcherId);
                        yield $server->stop();
                        Loop::stop();
                    });
                }
                
            }catch(\Throwable $e){
                print $e->getMessage() . ' in event-loop. file: ' . $e->getFile() . ' line ' . $e->getLine() . ' exiting loop and server ' . PHP_EOL;
                yield $server->stop();
                Loop::stop();
            }
        });
    }

    private function prepareServers(bool $cluster = false){
        if (gettype($this->servers) == 'array') {
            $listening_servers = array_map(function ($elem)use($cluster) {
                if($cluster) return Cluster::listen($elem);
                return Socket\Server::listen($elem);
            }, $this->servers);

        } elseif (gettype($this->servers) == 'string') {
            if($cluster) $listening_servers = [Cluster::listen($this->servers)];
            else $listening_servers = [Socket\Server::listen($this->servers)];
            
        } else {
            if($cluster) $listening_servers = [Cluster::listen('127.0.0.1:1337')];
            else $listening_servers = [Socket\Server::listen('127.0.0.1:1337')];
        }

        return $listening_servers;
    }

    private function get_logger($cluster = false){
        if($cluster){
            // Creating a log handler in this way allows the script to be run in a cluster or standalone.
            if (Cluster::isWorker()) {
                $handler = Cluster::createLogHandler();
            } else {
                $handler = new StreamHandler(ByteStream\getStdout());
                $handler->setFormatter(new ConsoleFormatter);
            }

            $logger = new Logger('worker-' . Cluster::getId());
            $logger->pushHandler($handler);
            return $logger;
        }else{
            $logHandler = new StreamHandler(ByteStream\getStdout());
            $logHandler->setFormatter(new ConsoleFormatter);
            $logger = new Logger('bots server');
            $logger->pushHandler($logHandler);
            return $logger;
        }
    }

    /**
     * cli-options
     * 
     * get info about running bots and handlers from console
     */
    private function cli_options(){
        $in = ByteStream\getStdin();

        while (($chunk = yield $in->read()) !== null) {
            $flag = false;
            switch (trim($chunk)) {
                case 'ls':
                    foreach ($this->files as $file_name => $h) {
                        print $file_name . ' active: ' . $h['active'] . '\n';
                    }
                    break;
                case 'll':
                    foreach ($this->files as $file_name => $h) {
                        print $file_name . PHP_EOL;
                        foreach ($h['handler'] as $key => $hh) {
                            if ($key == 'handlers') {
                                continue;
                            }
                            print $key . PHP_EOL;
                        }
                        foreach ($h['handler']->handlers as $h) {
                            print var_export($h) . PHP_EOL;
                        }
                    }
                    break;
                    // case 'reload':
                    //     // TODO: how to reload the files too
                    //     print 'restarting server...' . PHP_EOL;
                    //     yield $server->stop(2000);
                    //     Loop::stop();
                    //     $flag = true;
                    //     break;
                case 'exit':
                    exit();
                    break;
                default:
                    print 'can\'t understand you. available options are: exit/reload/ls/ll/^C' . PHP_EOL;
            }
            if ($flag) break;
        }
    }

    /**
     * get the path of request
     * load the update and file's handler using his config
     * then run the handlers
     */
    public function requestHandler($request, $logger){
        $path = ltrim($request->getUri()->getPath(), '/');

        if (!isset($this->files[$path])) {
            $logger->notice('file ' . $path . ' not exist');

        }elseif ($this->files[$path]['active']) {
            // debug
            $this->files[$path]['config']->debug && $logger->info('running file: ' . $path);

            try {
                $time = \Amp\Loop::now();

                $file = $this->files[$path];
                
                $update_class_name = $file['update_class_name'];
                $update_string = yield $request->getBody()->buffer();
                $update = new $update_class_name($file['config'], $update_string);

                parse_str($request->getUri()->getQuery(), $query);
                if (isset($query['token']))
                    $file['config']->token = $query['token'];

                $res = yield \Amp\call([$file['handler'], 'activate'], $file['config'], $update);
                
                $file['config']->debug && $logger->info('took: '.\Amp\Loop::now() - $time.'. handlers result', $res ?? []);

            } catch (\Throwable $e) {
                $logger->error( $e->getMessage() . ' when activate handlers in file ' . $e->getFile() . ' in line ' . $e->getLine() . '. path ' . $path . ' - disabled!');
                $this->files[$path]['active'] = 0;
            }
        }
    }
}
