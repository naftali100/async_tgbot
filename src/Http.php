<?php

namespace bot_lib;

use bot_lib\Update;

use Amp\Http\Client\Body\FormBody;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client;
use Amp\Promise;

use function Amp\call;


/**
 * http class
 */
class Http
{
    // public static $client;

    public function ApiRequest($method, $data = [])
    {
        $url = $this->config->server_url . $this->config->token . '/' . $method;
        return $this->Request($url, $data);
    }

    public function Request($url, $body = null): Response
    {
        $promise = call(function () use ($url, $body) {
            if ($body == null) {
                $request = new Client\Request($url);
            } else if (is_array($body)) {
                $request = new Client\Request($url, 'POST');
                $request->setBody(yield $this->BuildApiRequestBody($body));
            } else if (is_string($body) || (is_object($body) && get_class($body) == FormBody::class)) {
                $request = new Client\Request($url, 'POST');
                $request->setBody($body);
            } else if ($url instanceof Client\Request) {
                $request = $url;
            }

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
            $promise = $client->request($request);
            if (
                isset($this?->config) &&
                isset($this?->config?->apiErrorHandler) &&
                $this?->config?->apiErrorHandler != null
            ) {
                $promise->onResolve($this->config->apiErrorHandler);
            }
            if ($this?->config?->debug) {
                $promise->onResolve(function () use ($url, $time) {
                    print 'request to: ' . $url . ' took: ' . ($time - hrtime(1) * 1000 * 1000) . ' ms' . PHP_EOL;
                });
            }
            return $promise;
        });

        return new Response($promise, $this->config);
    }

    public function BuildApiRequestBody(array $data = [])
    {
        return call(function () use ($data) {
            $body = new FormBody;
            foreach ($data as $key => $value) {
                if (!empty($value)) {
                    if (in_array($key, ['document', 'photo', 'audio', 'thumb'])) {
                        if (yield \Amp\File\exists($value)) {
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
        });
    }
}
// Http::$client = HttpClientBuilder::buildDefault();

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
class Response implements \Amp\Promise
{
    private Promise $update_promise;

    public function __construct(private \Amp\Promise $request, private $config)
    {
        $this->update_promise = $this->get_update();
    }

    public function __get($key): Promise
    {
        switch ($key) {
            case 'result':
            case 'response':
            case 'json':
            case 'plain':
                return $this->get_plain_res();
                break;
            case 'decode':
            case 'array':
                return $this->get_decoded_res(true);
                break;
            case 'promise':
            case 'request':
                return $this->request;
                break;
            case 'update':
            default:
                return $this->update_promise;
        }
    }

    private function get_update()
    {
        $return_update = function ($req, $conf) {
            $res = yield $req;
            $res = yield $res->getBody()->buffer();
            return new Update($conf, $res);
        };
        return call($return_update, $this->request, $this->config);
    }

    private function get_plain_res()
    {
        $return_response = function ($req) {
            $res = yield $req;
            return yield $res->getBody()->buffer();
        };
        return call($return_response, $this->request);
    }

    private function get_decoded_res($array = false)
    {
        $return_decoded_response = function ($req) use ($array) {
            $res = yield $req;
            return json_decode((yield $res->getBody()->buffer()), $array);
        };
        return call($return_decoded_response, $this->request);
    }

    public function onResolve(callable $cb)
    {
        $this->update_promise->onResolve($cb);
    }
}
