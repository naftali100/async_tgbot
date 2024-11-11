<?php

namespace bot_lib;

class Config
{
  public function __construct(public $token = '', public $baseUrl = 'httpS://api.telegram.org/bot')
  {
  }
}
