<?php

namespace bot_lib;

use bot_lib\Update;

use Amp\Http\Client\Body\FormBody;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client;

use function Amp\call;


/**
 * http class
 */
class http
{
    public function ApiRequest($method, $data = [])
    {
        $url = $this->config->server_url . $this->config->token . '/' . $method;
        return $this->Request($url, $data, $this->config->async);
    }

    public function Request($url, $data = [], $async = true)
    {
        if ($async) {
            $client = HttpClientBuilder::buildDefault();
            if ($data != []) {
                $body = new FormBody;
                foreach ($data as $key => $value) {
                    if (in_array($key, ['document', 'photo', 'audio', 'thumb']) && !empty($value)) {
                        if (is_file($value)) {
                            \Amp\File\StatCache::clear($value);
                            $body->addFile($key, $value);
                        } else {
                            throw new \Error("file $value not exist");
                        }
                    } else {
                        if (!empty($value))
                            $body->addField($key, $value);
                    }
                }

                $request = new Client\Request($url, 'POST');
                $request->setBody($body);
                $request->setInactivityTimeout($this->config->fileRequestTimeout * 1000);
                $request->setTransferTimeout($this->config->fileRequestTimeout * 1000);
            } else if ($url instanceof Client\Request) {
                $request = $url;
            } else {
                $request = new Client\Request($url);
                if (str_ends_with(strtolower($url), 'getfile')) {
                    $request->setInactivityTimeout($this->config->fileRequestTimeout * 1000);
                    $request->setTransferTimeout($this->config->fileRequestTimeout * 1000);
                }
            }

            $promise = $client->request($request);
            if (isset($this->config->apiErrorHandler)) {
                $promise->onResolve($this->config->apiErrorHandler);
            }
            return new Response($promise, $this->config);
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            $res = curl_exec($ch);
            if (curl_error($ch)) {
                curl_close($ch);
                return false;
            } else {
                curl_close($ch);
                return $res;
            }
        }
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
class Response implements \Amp\Promise {
    public function __construct(private \Amp\Promise $request, private $config){ }

    public function __get($key)
    {
        switch ($key) {
            case 'result':
            case 'response':
                return $this->get_res();
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
                return $this->get_update();
        }
    }

    private function get_update(){
        $return_update = function($req, $conf){
            $res = yield $req;
            $res = yield $res->getBody()->buffer();
            return new Update($conf, $res);
        };
        return call($return_update, $this->request, $this->config);
    }

    private function get_res(){
        $return_response = function($req){
            $res = yield $req;
            return yield $res->getBody()->buffer();
        };
        return call($return_response, $this->request);
    }

    private function get_decoded_res($array = false){
        $return_decoded_response = function ($req) use($array) {
            $res = yield $req;
            return json_decode((yield $res->getBody()->buffer()), $array);
        };
        return call($return_decoded_response, $this->request);
    }
    
    public function onResolve(callable $cb)
    {
        $this->request->onResolve($cb);
    }
}
