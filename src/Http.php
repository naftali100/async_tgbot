<?php

namespace bot_lib;

use Amp\Http\Client\Form;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Monolog\Logger;

class Response implements \Stringable
{
    public function __construct(public $body, private Config $config)
    {
    }

    public function __get($type)
    {
        switch ($type) {
            case 'json':
            case 'decode':
                return json_decode($this->body, true);
            case 'string':
            case 'body':
                return $this->body;
            case 'update':
                $bot = new class ($this->config) extends \bot_lib\Bot {
                    public function handleUpdate(Update $update)
                    { // hack
                    }
                };
                return new Update($bot, $this->body);
            default:
                return json_decode($this->body, true)->$type;
        }
    }

    public function __toString(): string
    {
        return $this->body;
    }
}

class Http
{
    private $client;
    public function __construct(public Config $config, private Logger $log)
    {
        $this->client = HttpClientBuilder::buildDefault();
    }
    public function apiRequest($method, $data = [])
    {
        return new Update(new class ($this->config) extends \bot_lib\Bot {
            public function handleUpdate(Update $update)
            { // hack
            }
        }, $this->request($this->config->baseUrl . $this->config->token . '/' . $method, $data)->getBody()->buffer());
    }
    public function request($url, $body = null)
    {
        if ($this->config->debug) {
            $this->log->debug('request', ['url' => $url, 'body' => $body]);
        }

        $request = new Request($url);
        if ($body) {
            $requestBody = $this->buildApiRequestBody($body);
            $request->setBody($requestBody);
            $request->setMethod('POST');
            if (str_contains($requestBody->getContentType(), 'multipart')) {
                $request->setInactivityTimeout(30);
                $request->setTransferTimeout(30);
                $request->setBodySizeLimit(4 * 1024 * 1024 * 1024); // 4 GB
            }
        } else {
            $request->setMethod('GET');
        }
        return $this->client->request($request);
    }

    private function buildApiRequestBody(array $data = [])
    {
        $body = new Form();
        foreach ($data as $key => $value) {
            if (!empty($value)) {
                if (!is_string($value)) {
                    $value = json_encode($value);
                }
                if (in_array($key, ['document', 'photo', 'audio', 'thumbnail'])) {
                    if (\Amp\File\exists($value)) {
                        $body->addFile($key, $value);
                    } else {
                        throw new \Error("file $value not exist");
                    }
                } else {
                    $body->addField($key, $value);
                }
            }
        }

        return $body;
    }
}
