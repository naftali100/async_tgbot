<?php

use bot_lib\Bot;
use bot_lib\Update;

class EchoBot extends Bot
{
  public function handleUpdate(Update $update)
  {
    $update->reply($update->text);
  }
}
