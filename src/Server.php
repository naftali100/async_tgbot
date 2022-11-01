<?php

/**
 * the server that running all bots
 */

namespace bot_lib;

use Amp\Loop;
use Amp\ByteStream;
use Amp\Socket;
use Amp\Cluster\Cluster;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Status;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;
use Amp\Http\Server\StaticContent\DocumentRoot;

class Server extends Loader
{
    // public array $files = []; - set in Loader

    /** http server instance */
    private HttpServer $server;

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
            Loop::run(function () use ($as_cluster) {
                $this->runServer($as_cluster);
            });
        } else {
            return $this->runServer($as_cluster);
        }
    }

    private function runServer($as_cluster)
    {
        return \Amp\call(function () use ($as_cluster) {
            $servers = $as_cluster ? (yield $this->prepareServers($as_cluster)) : $this->prepareServers($as_cluster);

            $this->logger = $this->get_logger($as_cluster);
            $router = new Router;

            // $documentRoot = new DocumentRoot(__DIR__ . '/public');
            // $router->setFallback($documentRoot);

            // answer all request to avoid bot api resending failed updates
            $router->setFallback(new CallableRequestHandler(function (Request $request) {
                return new Response(Status::OK, [
                    'content-type' => 'text/plain; charset=utf-8'
                ], 'ok');
            }));

            foreach ($this->files as $path => $file) {
                $handler = new CallableRequestHandler(function (Request $request) use ($path, $file) {
                    try {
                        $time = \Amp\Loop::now();

                        $update_string = '';
                        while (($chunk = yield $request->getBody()->read()) !== null) {
                            $update_string .= $chunk;
                        }

                        // get token from request params if exist
                        parse_str($request->getUri()->getQuery(), $query);
                        if (isset($query['token'])) {
                            $file->config->token = $query['token'];
                        }

                        $update_class_name = $file->config->updateClassName;
                        $update = new $update_class_name($file->config, $update_string);

                        $file->config->logger->debug('server activating handlers');
                        $res = yield \Amp\call([$file->handler, 'activate'], $file->config, $update);

                        $file->config->logger->debug($path . ' took: ' . \Amp\Loop::now() - $time . '. handlers result', $res ?? []);
                    } catch (\Throwable $e) {
                        $file->config->logger->error('"' . $e->getMessage()  . '" when activate handlers in file ' . $e->getFile() . ':' . $e->getLine() . '. path: "' . $path . '" - disabled!');
                        $this->files[$path]->active = 0;
                    }
                    return new Response(Status::OK, [
                        'content-type' => 'text/plain; charset=utf-8'
                    ], 'ok');
                });

                $router->addRoute('POST', '/' . $path, $handler);
            }

            $this->server = new HttpServer($servers, $router, $this->logger);
            yield $this->server->start();

            if ($as_cluster) {
                // Stop the server when the worker is terminated.
                Cluster::onTerminate(function () {
                    return $this->server->stop();
                });
            } else {
                Loop::unreference(Loop::onSignal(\SIGINT, function (string $watcherId) {
                    Loop::cancel($watcherId);
                    yield $this->server->stop();
                    Loop::stop();
                }));
            }
        });
    }

    private function prepareServers(bool $cluster = false): array
    {
        $listening_servers = [];
        if (gettype($this->servers) == 'array') {
            $listening_servers = array_map(function ($elem) use ($cluster) {
                if ($cluster) {
                    return Cluster::listen($elem);
                } else {
                    return Socket\Server::listen($elem);
                }
            }, $this->servers);
        } elseif (gettype($this->servers) == 'string') {
            if ($cluster) {
                $listening_servers = [Cluster::listen($this->servers)];
            } else {
                $listening_servers = [Socket\Server::listen($this->servers)];
            }
        } else {
            if ($cluster) {
                $listening_servers = [Cluster::listen('127.0.0.1:1337')];
            } else {
                $listening_servers = [Socket\Server::listen('127.0.0.1:1337')];
            }
        }

        return $listening_servers;
    }

    private function get_logger($cluster = false)
    {
        if ($cluster) {
            // Creating a log handler in this way allows the script to be run in a cluster or standalone.
            if (Cluster::isWorker()) {
                $handler = Cluster::createLogHandler();
            } else {
                $handler = new StreamHandler(ByteStream\getStdout(), $this->logLevel);
                $handler->setFormatter(new ConsoleFormatter);
            }

            $logger = new Logger('worker-' . Cluster::getId());
            $logger->pushHandler($handler);
            return $logger;
        } else {
            $logHandler = new StreamHandler(ByteStream\getStdout(), $this->logLevel);
            $logHandler->setFormatter(new ConsoleFormatter);
            $logger = new Logger('bots server');
            $logger->pushHandler($logHandler);
            return $logger;
        }
    }

    public function stop()
    {
        return $this->server->stop();
    }

    public function getState()
    {
        return $this->server->getState();
    }

    public function setLogLevel($level = LogLevel::INFO)
    {
        $this->logLevel = $level;
    }
}
