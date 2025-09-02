<?php

namespace bot_lib;

use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\ByteStream;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

/**
 *
 * The main class for each bot
 *
 * an instance of this class will be created for each update
 *
 * the file name of your bot must be the same as the name of the class
 *
 */
abstract class Bot extends Api
{
    protected Logger $log;

    /**
     * create Bot class
     *
     * generally you don't need to override this, use the 'before' function
     *
     * @param \bot_lib\Config $config config
     * @param mixed $forUpdate use when overriding the constructor, if true class is instantiated for handling updates, else - for general requests or checks
     */
    public function __construct(public Config $config, private $forUpdate = false)
    {
        $logHandler = new StreamHandler(ByteStream\getStdout());
        $logHandler->pushProcessor(new PsrLogMessageProcessor());
        $logHandler->setFormatter(new ConsoleFormatter());
        $logHandler->setLevel($this->config->debug ? 'Debug' : 'Info');
        $logger = new Logger('Bot ' . get_class($this));
        $logger->pushHandler($logHandler);
        $this->log = $logger;
        $this->http = new Http($this->config, $this->log);
    }
    /**
     * the function that will be called to handle the update
     * @param \bot_lib\Update $update
     * @return void
     */
    abstract public function handleUpdate(Update $update);

    /**
     * optionally override this method to be called in case of an error
     * @param \Throwable $e
     * @return void
     */
    public function onError(\Throwable $e)
    {
    }

    /**
     * optionally override this function that will be called before handleUpdate
     */
    public function before(Update $update)
    {
    }

    /**
     * optionally override this function that will be called after handleUpdate
     */
    public function after(Update $update)
    {
    }

    /***********
     * API Methods
     ***********/

}
