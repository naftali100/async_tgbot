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
        private Loader $loader,
        $options = [
        'host' => "127.0.0.1",
        'port' => 1337,
        'reload' => false,
        'debug' => false
    ]
    ) {
        $this->options = new ServerOptions($options);
    }

    private function handleUpdate(Bot $bot, Update $update)
    {
        $reflector = new \ReflectionClass($bot);

        // check if method 'before' exist
        if ($reflector->getMethod('before')->getDeclaringClass()->getName() !== Bot::class) {
            $bot->before($update);
        }

        $bot->handleUpdate($update);

        foreach ($reflector->getMethods() as $method) {
            $attributes = $method->getAttributes(Filter\BaseFilter::class, \ReflectionAttribute::IS_INSTANCEOF);
            foreach ($attributes as $attr) {
                if (!$attr->newInstance()->validator->validate($update)) {
                    break;
                }
            }
            $bot->$method($update);
        }

        if ($reflector->getMethod('after')->getDeclaringClass()->getName() !== Bot::class) {
            $bot->after($update);
        }
    }

    public function run()
    {
        $logHandler = new StreamHandler(ByteStream\getStdout());
        $logHandler->pushProcessor(new PsrLogMessageProcessor());
        $logHandler->setFormatter(new ConsoleFormatter());
        $logHandler->setLevel('INFO');
        $logger = new Logger('server');
        $logger->pushHandler($logHandler);

        $server = SocketHttpServer::createForDirectAccess($logger);
        $errorHandler = new DefaultErrorHandler();

        $router = new Router($server, $logger, $errorHandler);

        foreach ($this->loader->bots as $path => $botOptions) {
            $router->addRoute('POST', "/{$path}", new ClosureRequestHandler(function (Request $request) use ($botOptions, $logger) {
                $bot = new $botOptions['class']($botOptions['config']);
                $update = new Update($bot, $request->getBody()->buffer());
                try {
                    $this->handleUpdate($bot, $update);
                } catch (\Throwable $e) {
                    $reflector = new \ReflectionMethod($bot, 'onError');
                    if ($reflector->getDeclaringClass()->getName() !== 'Bot') {
                        $bot->onError($e);
                    } else {
                        $logger->error('Error in: ' . get_class($bot) . ' '. $e->getMessage(), [$e]);
                    }
                }
                return new Response(
                    status: HttpStatus::OK,
                    headers: ['content-type' => 'text/plain'],
                    body: 'ok',
                );
            }));
            $logger->info('Bot loaded: ' . $path);
        }

        $url = new \Amp\Socket\InternetAddress($this->options->host, $this->options->port);
        $server->expose($url);

        $server->start($router, $errorHandler);

        // Await a termination signal to be received.
        $signal = trapSignal([\SIGHUP, \SIGINT, \SIGQUIT, \SIGTERM]);

        $logger->info(sprintf("Received signal %d, stopping HTTP server", $signal));

        $server->stop();
    }

    public function setWebhooks()
    {
        foreach ($this->loader->bots as $path => $botOptions) {
            $bot = new $botOptions['class']($botOptions['config']);
            $url = urlencode($this->options->host . $this->options->port . '/' . $path);
            $bot->setWebhook($url);
        }
    }
}
