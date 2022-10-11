<?php

/**
 * the server that running all bots
 */

namespace bot_lib;

// v3 uses
use Amp\Http\Server\SocketHttpServer;
use Amp\Http\Server\RequestHandler\ClosureRequestHandler;
use Amp\Http\Server\DefaultErrorHandler;

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
use Monolog\Processor\PsrLogMessageProcessor;
use Amp\Loop;
use Psr\Log\LogLevel;

class Server extends Loader
{
    // public array $files = []; - set in Loader

    /** http server instance */
    private SocketHttpServer $server;

    private $logLevel = LogLevel::NOTICE;
    private $logger;

    public function __construct($servers = null)
    {
        $this->servers = $servers;
    }

    public function run($startLoop = true)
    {
        $as_cluster = str_ends_with($argv[0] ?? "", "bin/cluster");
        if ($startLoop) {
            // Loop::run(function () use ($as_cluster) {
                $this->runServer($as_cluster);
                \Amp\trapSignal(SIGINT);
                $this->server->stop();
            // });
        } else {
            return $this->runServer($as_cluster);
        }
    }

    private function runServer($as_cluster)
    {
        // return \Amp\call(function () use ($as_cluster) {
            try {
                $servers = $as_cluster ? ($this->prepareServers($as_cluster)) : $this->prepareServers($as_cluster);

                $this->logger = $this->get_logger($as_cluster);

                // Set up a request handler.
                $this->server = new SocketHttpServer($this->logger);
                foreach ($servers as $ser) {
                    $this->server->expose($ser);
                }
                $this->server->onStart(function(){
                    $this->logger->notice('server started');
                });
                $this->server->onStop(function(){
                    $this->logger->notice('server stopped');
                });

                $this->server->start(new ClosureRequestHandler(function($request){
                    $this->requestHandler($request);

                    return new Response(
                        status: Status::OK,
                        headers: ["content-type" => "text/plain; charset=utf-8",],
                        body: "ok"
                    );
                }), new DefaultErrorHandler);
            } catch (\Throwable $e) {
                print '"' . $e->getMessage() . '" in event-loop. file: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
            }
        // });
    }

    private function prepareServers(bool $cluster = false): array
    {
        $listening_servers = [];
        if (gettype($this->servers) == 'array') {
            $listening_servers = array_map(function ($elem) use ($cluster) {
                // if ($cluster) {
                //     // return Cluster::listen($elem);
                // } else {
                    return Socket\SocketAddress\fromString($elem);
                // }
            }, $this->servers);
        } elseif (gettype($this->servers) == 'string') {
            // if ($cluster) {
            //     // $listening_servers = [Cluster::listen($this->servers)];
            // } else {
                $listening_servers = [Socket\SocketAddress\fromString($this->servers)];
            // }
        } else {
            // if ($cluster) {
                // $listening_servers = [Cluster::listen('127.0.0.1:1337')];
            // } else {
                $listening_servers = [Socket\SocketAddress\fromString('127.0.0.1:1337')];
            // }
        }

        return $listening_servers;
    }

    private function get_logger($cluster = false)
    {
        // if ($cluster) {
        //     // Creating a log handler in this way allows the script to be run in a cluster or standalone.
        //     // if (Cluster::isWorker()) {
        //     //     $handler = Cluster::createLogHandler();
        //     // } else {
        //         $handler = new StreamHandler(ByteStream\getStdout(), $this->logLevel);
        //         $handler->setFormatter(new ConsoleFormatter);
        //     // }

        //     $logger = new Logger('worker-' . Cluster::getId());
        //     $logger->pushHandler($handler);
        //     return $logger;
        // } else {
            $logHandler = new StreamHandler(ByteStream\getStdout(), $this->logLevel);
            $logHandler->pushProcessor(new PsrLogMessageProcessor());
            $logHandler->setFormatter(new ConsoleFormatter);
            $logger = new Logger('bots server');
            $logger->pushHandler($logHandler);

            return $logger;
        // }
    }

    /**
     * cli-options
     * 
     * get info about running bots and handlers from console
     */
    private function cli_options()
    {
        $in = ByteStream\getStdin();

        while (($chunk =  $in->read()) !== null) {
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
                    //      $server->stop(2000);
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
    public function requestHandler($request)
    {
        $path = ltrim($request->getUri()->getPath(), '/');

        if (!isset($this->files[$path])) {
            $this->logger->notice('file ' . $path . ' not exist');
        } elseif ($this->files[$path]->active) {
            $this->logger->info('running file: ' . $path);

            try {
                $time = \Amp\now();
                /**
                 * @var BotFile
                 */
                $file = $this->files[$path];

                $update_string = '';

                while (($chunk =  $request->getBody()->read()) !== null) {
                    $update_string .= $chunk;
                }

                $update_class_name = $file->config->updateClassName;
                $update = new $update_class_name($file->config, $update_string);

                // get token from request params if exist
                parse_str($request->getUri()->getQuery(), $query);
                if (isset($query['token'])) {
                    $file->config->token = $query['token'];
                }

                $file->config->logger->debug('server activating handlers');
                $res =  $file->handler->activate($file->config, $update);
                // $res =  \Amp\call([$file->handler, 'activate'], $file->config, $update);

                $file->config->logger->debug($path . ' took: ' . \Amp\now() - $time . '. handlers result', $res ?? []);
            } catch (\Throwable $e) {
                $file->config->logger->error('"' . $e->getMessage()  . '" when activate handlers in file ' . $e->getFile() . ':' . $e->getLine() . '. path: "' . $path . '" - disabled!');
                $this->files[$path]->active = 0;
            }
        }
    }

    public function stop()
    {
        return $this->server->stop();
    }

    public function getState()
    {
        return $this->server->getStatus();
    }

    public function setLogLevel($level = LogLevel::INFO)
    {
        $this->logLevel = $level;
    }
}
