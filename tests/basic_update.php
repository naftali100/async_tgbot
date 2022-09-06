<?php

require_once __DIR__ . '/../vendor/autoload.php';

use bot_lib\Update;
use bot_lib\Config;

trait UpdateTypes
{
    public $user_id = 0000;
    public $chat_id = 1111;

    public $myUserId = 227774988; // set it to the chat that receive messages during testing

    public Update $private_message;
    public Update $group_message;
    public Update $edited_message;
    public Update $new_member;
    public Update $cbq;
    public Update $inline_query;
    public Update $channel_message;
    public Update $forwarded_message;

    public function init()
    {
        $this->config = new Config();
        $this->config->load(__DIR__ . '/conf.json');
        $this->private_message = new Update($this->config, '{
            "update_id": 933205645,
            "message": {
             "message_id": 1171914,
             "from": {
              "id": ' . $this->user_id . ',
              "is_bot": false,
              "first_name": "Naftali",
              "username": "Naftali100",
              "language_code": "en",
              "is_premium": true
             },
             "chat": {
              "id": ' . $this->chat_id . ',
              "first_name": "Naftali",
              "username": "Naftali100",
              "type": "private"
             },
             "date": 1662391289,
             "text": "text"
            }
           }');
        $this->group_message = new Update($this->config, '{
            "update_id": 933205654,
            "message": {
             "message_id": 21845,
             "from": {
              "id": ' . $this->user_id . ',
              "is_bot": false,
              "first_name": "Naftali",
              "username": "Naftali100",
              "language_code": "en",
              "is_premium": true
             },
             "chat": {
              "id": ' . $this->chat_id . ',
              "title": "בדיקות #vid vid#",
              "username": "n_tests",
              "type": "supergroup"
             },
             "date": 1662391405,
             "text": "text",
             "has_protected_content": true
            }
           }');

        $this->edited_message = new Update($this->config, '{
            "update_id": 933205661,
            "edited_message": {
             "message_id": 1171917,
             "from": {
              "id": ' . $this->user_id . ',
              "is_bot": false,
              "first_name": "Naftali",
              "username": "Naftali100",
              "language_code": "en",
              "is_premium": true
             },
             "chat": {
              "id": ' . $this->myUserId . ',
              "first_name": "Naftali",
              "username": "Naftali100",
              "type": "private"
             },
             "date": 1662391377,
             "edit_date": 1662391612,
             "text": "text"
            }
           }');

        $this->forwarded_message = new Update($this->config, '{
            "update_id": 933205670,
            "message": {
             "message_id": 1171928,
             "from": {
              "id": ' . $this->user_id . ',
              "is_bot": false,
              "first_name": "Naftali",
              "username": "Naftali100",
              "language_code": "en",
              "is_premium": true
             },
             "chat": {
              "id": ' . $this->myUserId . ',
              "first_name": "Naftali",
              "username": "Naftali100",
              "type": "private"
             },
             "date": 1662391729,
             "forward_from": {
              "id": ' . $this->myUserId . ',
              "is_bot": false,
              "first_name": "Naftali",
              "username": "Naftali100",
              "language_code": "en",
              "is_premium": true
             },
             "forward_date": 1662391725,
             "text": "text"
            }
           }');
        $this->cbq = new Update(
            $this->config,
            '
        {
            "update_id": 883655366,
            "callback_query": {
                "message_id": 10321,
                "from": {
                    "id": ' . $this->user_id . ',
                    "is_bot": true,
                    "is_deleted": false,
                    "first_name": "local test",
                    "username": "local_tgbot",
                    "user_status": "online"
                },
                "chat": {
                    "id": ' . $this->chat_id . ',
                    "first_name": "Naftali",
                    "username": "Naftali100",
                    "type": "private",
                    "user_status": "recently"
                },
                "date": 1662490245,
                "text": "text",
                "reply_markup": {
                    "inline_keyboard": [
                        [
                            {
                                "text": "row 1 col 1",
                                "callback_data": "row_1_col_1"
                            },
                            {
                                "text": "row 1 col 2",
                                "callback_data": "row_1_col_2"
                            }
                        ],
                        [
                            {
                                "text": "row 2 col 1",
                                "callback_data": "row_2_col_1"
                            },
                            {
                                "text": "row 2 col 2",
                                "callback_data": "row_2_col_2"
                            }
                        ],
                        [
                            {
                                "text": "hello",
                                "callback_data": "hello"
                            }
                        ],
                        [
                            {
                                "text": "url",
                                "url": "https://google.com/"
                            }
                        ],
                        [
                            {
                                "text": "web app",
                                "web_app": {
                                    "url": "https://google.com/"
                                }
                            }
                        ]
                    ]
                }
            }
        }
        '
        );
        $this->inline_query = new Update($this->config, '{
            "update_id": 933208702,
            "inline_query": {
             "id": "978286125037432537",
             "from": {
              "id": ' . $this->user_id . ',
              "is_bot": false,
              "first_name": "Naftali",
              "username": "Naftali100",
              "language_code": "en",
              "is_premium": true
             },
             "chat_type": "sender",
             "query": "text",
             "offset": ""
            }
           }');
        // $this->channel_message = new Update($this->config, json_encode([]));
        // $this->new_member = new Update($this->config, json_encode([]));
        // $this->document_file = new Update($this->config, json_encode([]));
        // $this->photo_file = new Update($this->config, json_encode([]));
        // $this->music_file = new Update($this->config, json_encode([]));
        // $this->sticker_file = new Update($this->config, json_encode([]));
    }
}
