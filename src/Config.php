<?php

namespace bot_lib;

/**
 * config of the bot
 */
class Config
{
    /**
     * @var $parseMode how the text will be parsed
     */

    public $ParseMode;

    /**
     * @var $server the base url of the server to send api requests.
     */
    public $server_url = 'https://api.telegram.org/bot';

    /**
     * @var $noUpdate to use the bot without update
     */
    public $noUpdate;

    /**
     * @var $updateClassName you can override the class that will be sent to handlers with your own extension of Update
     */
    public $updateClassName = Update::class;

    /**
     * @var $webPreview whether or not show preview of links 
     */
    public $webPagePreview = false;

    /** 
     * @var $async send requests asynchronously 
     */
    public $async = true;

    /**
     * @var $debug show debug info
     */
    public $debug = false;

    /**
     * @var $useDB if set to true store keyboard in db. requires redBean  
     */
    public $useDB = false;

    public $Notification = false;

    /**
     * callback function that will run after every api request when it resolve. the function should take 2 params $error and $result
     */
    public $apiErrorHandler = null;

    /**
     * timeout and inactivity time for upload and download requests.
     */
    public $fileRequestTimeout = 30;

    public function __construct(public $token = null)
    {
    }

    /**
     * json with settings as they in the class
     * @param $file path of settings json file
     */
    public function load($file)
    {
        $conf = json_decode(file_get_contents($file), true);
        foreach ($conf as $key => $value) {
            $this->$key = $value;
        }
    }
}
