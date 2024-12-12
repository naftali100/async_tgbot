<?php

/**
 * the server that running all bots
 */

namespace bot_lib;

use Amp\ByteStream;
use Amp\Http\HttpStatus;
use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler\ClosureRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Server\SocketHttpServer;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

use function Amp\trapSignal;

class ServerOptions
{
    public function __construct(
        $options = [
        'host' => "127.0.0.1",
        'port' => 1337,
        'reload' => false,
        'debug' => false
    ]
    ) {
        $this->host = $options["host"] ?? "";
        $this->port = $options["port"] ?? 1337;
        $this->reload = $options["reload"] ?? false;
        $this->debug = $options["debug"] ?? false;
    }
    public $host = '127.0.0.1';
    public $port = 1337;
    public $reload = false;
    public $debug = false;
}

class Server
{
    private ServerOptions $options;

    public function __construct(
        private Loader $bots,
        $options = [
        'host' => "127.0.0.1",
        'port' => 1337,
        'reload' => false,
        'debug' => false
    ]
    ) {
        $this->options = new ServerOptions($options);
    }
    public function run()
    {
        $logHandler = new StreamHandler(ByteStream\getStdout());
        $logHandler->pushProcessor(new PsrLogMessageProcessor());
        $logHandler->setFormatter(new ConsoleFormatter());
        $logger = new Logger('server');
        $logger->pushHandler($logHandler);

        $server = SocketHttpServer::createForDirectAccess($logger);
        $errorHandler = new DefaultErrorHandler();

        $router = new Router($server, $logger, $errorHandler);

        foreach ($this->bots->bots as $path => $bot) {
            $router->addRoute('POST', "/{$path}", new ClosureRequestHandler(function (Request $request) use ($bot) {
                $update = new Update($bot, $request->getBody()->buffer());
                $bot->handleUpdate($update);
                return new Response(
                    status: HttpStatus::OK,
                    headers: ['content-type' => 'text/plain'],
                    body: 'ok',
                );
            }));
        }

        $url = new \Amp\Socket\InternetAddress($this->options->host, $this->options->port);
        $server->expose($url);

        $server->start($router, $errorHandler);

        // Await a termination signal to be received.
        $signal = trapSignal([\SIGHUP, \SIGINT, \SIGQUIT, \SIGTERM]);

        $logger->info(sprintf("Received signal %d, stopping HTTP server", $signal));

        $server->stop();
    }
}
