<?php

namespace bot_lib;

use Amp\Http\Client\Form;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;

class Http
{
  private $client;
  public function __construct(public Config $config)
  {
    $this->client = HttpClientBuilder::buildDefault();
  }
  public function apiRequest($method, $data = [])
  {
    return $this->request($this->config->baseUrl . $this->config->token . '/' . $method, $data);
  }
  public function request($url, $body)
  {
    $response = $this->client->request(new Request($url, $body ? 'POST' : 'GET', $body ? $this->buildApiRequestBody($body) : null));
    return $response->getBody()->buffer();
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
