<?php

require_once __DIR__ . '/../vendor/autoload.php';

use bot_lib\Update;
use bot_lib\Config;

trait UpdateTypes
{
    public $user_id = 0000;
    public $chat_id = 1111;
    public $channel_id = 2222;

    public $username = 'username';

    public $myUserId = 227774988; // set it to the chat that receive messages during testing

    public Update $private_message;
    public Update $private_with_ent;
    public Update $group_message;
    public Update $edited_message;
    public Update $new_member;
    public Update $cbq;
    public Update $inline_query;
    public Update $channel_message;
    public Update $forwarded_message;
    public Update $pin_message;
    public Update $sender_chat;
    public Update $photo_file;
    public Update $join_request;
    public Update $webapp_data;

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
              "username": "' . $this->username . '",
              "language_code": "en",
              "is_premium": true
             },
             "chat": {
              "id": ' . $this->user_id . ',
              "first_name": "Naftali",
              "username": "username",
              "type": "private"
             },
             "date": 1662391289,
             "text": "text"
            }
           }');
        $this->group_message = new Update($this->config, '
        {
            "update_id": 933205654,
            "message": {
             "message_id": 21845,
             "from": {
              "id": ' . $this->user_id . ',
              "is_bot": false,
              "first_name": "Naftali",
              "username": "username",
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
              "username": "username",
              "language_code": "en",
              "is_premium": true
             },
             "chat": {
              "id": ' . $this->chat_id . ',
              "first_name": "Naftali",
              "username": "username",
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
              "username": "username",
              "language_code": "en",
              "is_premium": true
             },
             "chat": {
              "id": ' . $this->myUserId . ',
              "first_name": "Naftali",
              "username": "username",
              "type": "private"
             },
             "date": 1662391729,
             "forward_from": {
              "id": ' . $this->myUserId . ',
              "is_bot": false,
              "first_name": "Naftali",
              "username": "username",
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
                "update_id": 883655399,
                "callback_query": {
                    "id": "978286124481878657",
                    "from": {
                        "id": ' . $this->user_id . ',
                        "is_bot": false,
                        "is_deleted": false,
                        "first_name": "Naftali",
                        "username": "username",
                        "language_code": "en",
                        "is_premium": true,
                        "user_status": "recently"
                    },
                    "message": {
                        "message_id": 11122,
                        "from": {
                            "id": 123123123,
                            "is_bot": true,
                            "is_deleted": false,
                            "first_name": "local test",
                            "username": "local_tgbot",
                            "user_status": "online"
                        },
                        "chat": {
                            "id": ' . $this->chat_id . ',
                            "first_name": "Naftali",
                            "username": "username",
                            "type": "private",
                            "user_status": "recently"
                        },
                        "date": 1662724675,
                        "text": "text",
                        "reply_markup": {
                            "inline_keyboard": [
                                [
                                    {
                                        "text": "btn",
                                        "callback_data": "data"
                                    }
                                ]
                            ]
                        }
                    },
                    "chat_instance": "-7164038307691258958",
                    "data": "data"
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
              "username": "username",
              "language_code": "en",
              "is_premium": true
             },
             "chat_type": "sender",
             "query": "text",
             "offset": ""
            }
           }');
        $this->new_member = new Update($this->config, '{
            "update_id": 933211621,
            "message": {
             "message_id": 21858,
             "from": {
              "id": ' . $this->user_id . ',
              "is_bot": false,
              "first_name": "avi",
              "username": "aaaaaa"
             },
             "chat": {
              "id": ' . $this->chat_id . ',
              "title": "chat name",
              "username": "group_chat",
              "type": "supergroup"
             },
             "date": 1662587549,
             "new_chat_participant": {
              "id": ' . $this->user_id . ',
              "is_bot": false,
              "first_name": "avi",
              "username": "aaaaa"
             },
             "new_chat_member": {
              "id": ' . $this->user_id . ',
              "is_bot": false,
              "first_name": "avi",
              "username": "aaa"
             },
             "new_chat_members": [
              {
               "id": ' . $this->user_id . ',
               "is_bot": false,
               "first_name": "avi",
               "username": "aaaa"
              }
             ],
             "has_protected_content": true
            }
           }');
        $this->pin_message = new Update(
            $this->config,
            '{
                "update_id": 883655407,
                "message": {
                    "message_id": 21868,
                    "from": {
                        "id": ' . $this->user_id . ',
                        "is_bot": false,
                        "is_deleted": false,
                        "first_name": "Naftali",
                        "username": "username",
                        "language_code": "en",
                        "is_premium": true,
                        "user_status": "recently"
                    },
                    "chat": {
                        "id": ' . $this->chat_id . ',
                        "title": "בדיקות #vid vid#",
                        "username": "n_tests",
                        "type": "supergroup"
                    },
                    "date": 1662732485,
                    "pinned_message": {
                        "message_id": 21863,
                        "from": {
                            "id": ' . $this->user_id . ',
                            "is_bot": false,
                            "is_deleted": false,
                            "first_name": "Naftali",
                            "username": "username",
                            "language_code": "en",
                            "is_premium": true,
                            "user_status": "recently"
                        },
                        "chat": {
                            "id": ' . $this->chat_id . ',
                            "title": "בדיקות #vid vid#",
                            "username": "n_tests",
                            "type": "supergroup"
                        },
                        "date": 1662732244,
                        "text": "text",
                        "has_protected_content": true
                    },
                    "has_protected_content": true
                }
            }'
        );
        $this->sender_chat = new Update($this->config, '
        {
            "update_id": 933240418,
            "message": {
             "message_id": 21886,
             "from": {
              "id": 136817688,
              "is_bot": true,
              "first_name": "Channel",
              "username": "Channel_Bot"
             },
             "sender_chat": {
              "id": ' . $this->channel_id . ',
              "title": "האנונימי 😈",
              "username": "username1",
              "type": "channel"
             },
             "chat": {
              "id": ' . $this->chat_id . ',
              "title": "בדיקות #vid vid#",
              "username": "n_tests",
              "type": "supergroup"
             },
             "date": 1663481470,
             "text": "text"
            }
           }');
        $this->photo_file = new Update($this->config, '
        {
            "update_id": 933261123,
            "message": {
             "message_id": 1190691,
             "from": {
              "id": ' . $this->user_id . ',
              "is_bot": false,
              "first_name": "Naftali",
              "username": "Naftali100",
              "language_code": "en"
             },
             "chat": {
              "id": 227774988,
              "first_name": "Naftali",
              "username": "Naftali100",
              "type": "private"
             },
             "date": 1664048548,
             "photo": [
              {
               "file_id": "AgACAgQAAxkBAAESKyNjL12kj17pLPrpHSdWMaWizTDgCAACjLwxG7n3gFHY8mnx8t4dnQEAAwIAA3MAAykE",
               "file_unique_id": "AQADjLwxG7n3gFF4",
               "file_size": 1093,
               "width": 60,
               "height": 90
              },
              {
               "file_id": "AgACAgQAAxkBAAESKyNjL12kj17pLPrpHSdWMaWizTDgCAACjLwxG7n3gFHY8mnx8t4dnQEAAwIAA20AAykE",
               "file_unique_id": "AQADjLwxG7n3gFFy",
               "file_size": 14400,
               "width": 214,
               "height": 320
              },
              {
               "file_id": "AgACAgQAAxkBAAESKyNjL12kj17pLPrpHSdWMaWizTDgCAACjLwxG7n3gFHY8mnx8t4dnQEAAwIAA3gAAykE",
               "file_unique_id": "AQADjLwxG7n3gFF9",
               "file_size": 50824,
               "width": 534,
               "height": 800
              },
              {
               "file_id": "AgACAgQAAxkBAAESKyNjL12kj17pLPrpHSdWMaWizTDgCAACjLwxG7n3gFHY8mnx8t4dnQEAAwIAA3kAAykE",
               "file_unique_id": "AQADjLwxG7n3gFF-",
               "file_size": 108867,
               "width": 854,
               "height": 1280
              },
              {
               "file_id": "AgACAgQAAxkBAAESKyNjL12kj17pLPrpHSdWMaWizTDgCAACjLwxG7n3gFHY8mnx8t4dnQEAAwIAA3cAAykE",
               "file_unique_id": "AQADjLwxG7n3gFF8",
               "file_size": 348614,
               "width": 1708,
               "height": 2560
              }
             ]
            }
           }');
        $this->join_request = new Update($this->config, '
        {
            "update_id": 883655480,
            "chat_join_request": {
                "chat": {
                    "id": ' . $this->chat_id . ',
                    "title": "בדיקות #vid vid#",
                    "username": "username",
                    "type": "supergroup"
                },
                "from": {
                    "id": ' . $this->user_id . ',
                    "is_bot": false,
                    "is_deleted": false,
                    "first_name": "Jam",
                    "language_code": "en",
                    "user_status": "recently"
                },
                "date": 1663503683
            }
        }');
        $this->private_with_ent = new Update($this->config, '
        {
            "update_id": 933270796,
            "message": {
             "message_id": 1193322,
             "from": {
              "id": ' . $this->user_id . ',
              "is_bot": false,
              "first_name": "Naftali",
              "username": "Naftali100",
              "language_code": "en"
             },
             "chat": {
              "id": ' . $this->user_id . ',
              "first_name": "Naftali",
              "username": "Naftali100",
              "type": "private"
             },
             "date": 1664306176,
             "text": "text",
             "entities": [
              {
               "offset": 0,
               "length": 4,
               "type": "bold"
              },
              {
               "offset": 0,
               "length": 4,
               "type": "italic"
              }
             ]
            }
           }');
        $this->webapp_data = new Update($this->config, '{
            "update_id": 855118011,
            "message": {
                "message_id": 368000,
                "from": {
                    "id": '.$this->user_id.',
                    "is_bot": false,
                    "is_deleted": false,
                    "first_name": "Naftali",
                    "username": "Naftali100",
                    "language_code": "en",
                    "user_status": "recently"
                },
                "chat": {
                    "id": '.$this->user_id.',
                    "first_name": "Naftali",
                    "username": "Naftali100",
                    "type": "private",
                    "user_status": "recently"
                },
                "date": 1666731384,
                "web_app_data": {
                    "button_text": "btn data",
                    "data": "data"
                }
            }
        }');
        // $this->document_file = new Update($this->config, json_encode([]));
        // $this->photo_file = new Update($this->config, json_encode([]));
        // $this->music_file = new Update($this->config, json_encode([]));
        // $this->sticker_file = new Update($this->config, json_encode([]));
    }
}
