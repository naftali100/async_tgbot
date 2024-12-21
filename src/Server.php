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

function botAutoloader($class_name)
{
    set_error_handler(function () { /* ignore errors */
    });
    include_once getcwd() . '/' . $class_name . '.php';
    include_once getcwd() . '/' . strtolower($class_name) . '.php';
    include_once getcwd() . '/' . camelToSnake($class_name) . '.php';
    include_once getcwd() . '/' . lcfirst(camelToSnake($class_name)) . '.php';

    include_once getcwd() . '/bots/' . $class_name . '.php';
    include_once getcwd() . '/bots/' . strtolower($class_name) . '.php';
    include_once getcwd() . '/bots/' . camelToSnake($class_name) . '.php';
    include_once getcwd() . '/bots/' . lcfirst(camelToSnake($class_name)) . '.php';
    restore_error_handler();
}

class ServerOptions
{
    public function __construct(
        $options = [
        'host' => '127.0.0.1',
        'port' => 1337,
        'reload' => false,
        'debug' => false
    ]
    ) {
        $this->host = $options["host"] ?? "127.0.0.1";
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
    private Logger $logger;

    /**
     * an array containing the path config and class of each bot
     *
     * example:
     * {
     *     'path' => [
     *          'config' => Config,
     *          'class' => EchoBot
     * }
     * @var
     */
    private $bots;

    public function __construct(
        $options = [
        'host' => '127.0.0.1',
        'port' => 1337,
        'reload' => false,
        'debug' => false
    ]
    ) {
        $this->options = new ServerOptions($options);
        $logHandler = new StreamHandler(ByteStream\getStdout());
        $logHandler->pushProcessor(new PsrLogMessageProcessor());
        $logHandler->setFormatter(new ConsoleFormatter());
        $logHandler->setLevel($this->options->debug ? 'Debug' : 'Info');
        $logger = new Logger('server');
        $logger->pushHandler($logHandler);
        $this->logger = $logger;

        //
        // register class loader
        //
        function camelToSnake($camelCase)
        {
            $pattern = '/(?<=\\w)(?=[A-Z])|(?<=[a-z])(?=\d)/';
            $snakeCase = preg_replace($pattern, '_', $camelCase);
            return strtolower($snakeCase);
        }
    }

    /**
     * load a bot
     * @param string $path the url path of the webhook for this bot in this server
     * @param string $botClass the className for the bot use `BotClass::class` to get it
     * @param \bot_lib\Config $config config loaded via `Config::fromJsonFile($path)` or `Config::fromEnvFile($path)`
     * @throws \Error
     * @return void
     */
    public function load(string $path, string $botClass, Config $config)
    {
        spl_autoload_register('bot_lib\botAutoloader');
        $botInstance = new $botClass($config);
        if (!$botInstance instanceof Bot) {
            throw new \Error('invalid class '. get_class($botInstance) . '. all classes should extend the Bot abstract class');
        }
        $this->bots[$path] = ['class' => $botClass, 'config' => $config];
        spl_autoload_unregister('bot_lib\botAutoloader');
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
            if(!$attributes){
                break;
            }
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
        $logger = $this->logger;

        $server = SocketHttpServer::createForDirectAccess($logger);
        $server->expose(new \Amp\Socket\InternetAddress($this->options->host, $this->options->port));
        $server->expose(new \Amp\Socket\InternetAddress(gethostbyname(gethostname()), $this->options->port));

        $errorHandler = new DefaultErrorHandler();

        $router = new Router($server, $logger, $errorHandler);

        foreach ($this->bots as $path => $botOptions) {
            $router->addRoute('POST', "/{$path}", new ClosureRequestHandler(function (Request $request) use ($botOptions, $logger) {
                $logger->debug('new request', ['path' => $request->getUri()->getPath()]);
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
            $logger->info("bot {$botOptions['class']} stated in path: {$path}");
        }

        $server->start($router, $errorHandler);

        // Await a termination signal to be received.
        $signal = trapSignal([\SIGHUP, \SIGINT, \SIGQUIT, \SIGTERM]);

        $logger->info(sprintf("Received signal %d, stopping HTTP server", $signal));

        $server->stop();
    }

    /**
     * set all bots webhook to current server
     * @param string $webhookTargetUrl the url to sent the webhooks to
     * @return void
     */
    public function setWebhooks(string $webhookTargetUrl = null)
    {
        foreach ($this->bots as $path => $botOptions) {
            $this->logger->debug("setting webhook for {$path}");
            $bot = new $botOptions['class']($botOptions['config']);
            $url = 'http://' . (gethostname() ?? $this->options->host) . ':' . $this->options->port . '/' . $path;
            $res = $bot->setWebhook($webhookTargetUrl ?? $url);
            $this->logger->debug($res, [$url]);
        }
    }
}
