<?php

namespace bot_lib;

use bot_lib\Update;

use Amp\Http\Client\Body\FormBody;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client;
use Amp\Http\Client\Response as AmpRes;

/**
 * http class
 */
class Http
{
    public function ApiRequest($method, $data = [])
    {
        $this->config->logger->notice('making request');
        $url = $this->config->server_url . $this->config->token . '/' . $method;
        return $this->Request($url, $data);
    }

    public function Request($url, $body = null): Response
    {
        if ($body == null) {
            $request = new Client\Request($url);
        } else if (is_array($body)) {
            $request = new Client\Request($url, 'POST');
            $request->setBody($this->BuildApiRequestBody($body));
        } else if (is_string($body) || (is_object($body) && get_class($body) == FormBody::class)) {
            $request = new Client\Request($url, 'POST');
            $request->setBody($body);
        } else if ($url instanceof Client\Request) {
            $request = $url;
        }


        $this->config->logger->notice('request body prepared');

        if ($this->config->debug > 1) {
            var_dump($url);
        }

        if (str_ends_with(strtolower($url), 'getfile') || str_ends_with(strtolower($url), 'sendaudio')) {
            $request->setInactivityTimeout($this->config->fileRequestTimeout * 1000);
            $request->setTransferTimeout($this->config->fileRequestTimeout * 1000);
            $request->setBodySizeLimit(2 * 1024 * 1024 * 1024); // 2 GB
        }

        $time = hrtime(1);

        $client = HttpClientBuilder::buildDefault();
        $result = $client->request($request);

        return new Response($result, $this->config);
    }

    public function BuildApiRequestBody(array $data = [])
    {
        $body = new FormBody;
        foreach ($data as $key => $value) {
            if (!empty($value)) {
                if (!is_string($value)) {
                    $value = json_encode($value);
                }
                if (in_array($key, ['document', 'photo', 'audio', 'thumb'])) {
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

/**
 * response to http request. can yield different result. 
 * 
 * to yield different result. yield the prop of the Response object. yield $response->prop.
 * 
 * `result|response` - to yield plain result.
 *  
 * `decoded|array` - to yield decoded json of the result.
 * 
 * `promise|request` - to yield amp's response object.
 * 
 * `update` - to yield result in update object.
 * 
 * by default the Response promise yields Update object.
 * 
 * @yield Amp\Result|Update|string|array 
 */
class Response
{
    private AmpRes $response;
    private string $responseBodyString;

    public function __construct(AmpRes $response, private $config)
    {
        $this->response = $response;
    }

    public function getResponse(): AmpRes
    {
        return $this->response;
    }

    public function __get($key)
    {
        switch ($key) {
            case 'result':
            case 'response':
            case 'json':
            case 'plain':
                return $this->getPlainRes();
                break;
            case 'decode':
            case 'array':
                return $this->getDecodedRes(true);
                break;
                // case 'promise':
                // case 'request':
                //     return $this->request;
                //     break;
            case 'update':
                return $this->getUpdate();
                break;
            default:
                return $this->getUpdate()->{$key};
        }
    }

    public function __call($name, $arguments)
    {
        return $this->response->{$name}(...$arguments);
    }

    public function getUpdate()
    {
        $requestBody = Helpers::cast(FormBody::class, $this->response->getRequest()->getBody());
        $resultBody = $this->getPlainRes();
        $update = new Update($this->config, $resultBody);
        $update->request_info = [
            'url' => $this->response->getRequest()->getUri(),
            'request_body' => $requestBody->getFields()
        ];
        return $update;
    }

    public function getPlainRes()
    {
        if(!isset($this->responseBodyString)){
            $this->responseBodyString = $this->response->getBody()->buffer();
        }
        return $this->responseBodyString;
    }

    public function getDecodedRes($array = true)
    {
        return json_decode($this->getPlainRes(), $array);
    }
}
