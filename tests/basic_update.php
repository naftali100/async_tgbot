<?php

// require __DIR__ . '/../src/bot_lib.php';

use bot_lib\Update;
use bot_lib\Config;

trait UpdateTypes
{
    public $user_id = 0000;
    public $chat_id = 1111;

    public $private_message;
    public $group_message;
    public $edited_message;
    public $new_member;
    public $cbq;
    public $channel_message;
    public $forwarded_message;

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
              "id": 227774988,
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
              "id": 227774988,
              "first_name": "Naftali",
              "username": "Naftali100",
              "type": "private"
             },
             "date": 1662391729,
             "forward_from": {
              "id": 227774988,
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
        // $this->cbq = new Update(
        //     $this->config,
        //     <<<END
        // {
        //     "update_id": 854947025,
        //     "callback_query": {
        //         "id": "4581105008550348206",
        //         "from": {
        //             "id": 5361588923,
        //             "is_bot": false,
        //             "is_deleted": false,
        //             "first_name": "אבי",
        //             "last_name": "מרדכי",
        //             "language_code": "he",
        //             "user_status": "recently"
        //         },
        //         "message": {
        //             "message_id": 350078,
        //             "from": {
        //                 "id": 1568181297,
        //                 "is_bot": true,
        //                 "is_deleted": false,
        //                 "first_name": "חיפוש סרטים",
        //                 "username": "movie_4_you_tgbot",
        //                 "user_status": "online"
        //             },
        //             "chat": {
        //                 "id": -1001451090121,
        //                 "title": "סרטים שירים סדרות",
        //                 "username": "SeretPlus",
        //                 "type": "supergroup"
        //             },
        //             "date": 1662225931,
        //             "edit_date": 1662234210,
        //             "reply_to_message": {
        //                 "message_id": 350077,
        //                 "from": {
        //                     "id": 5361588923,
        //                     "is_bot": false,
        //                     "is_deleted": false,
        //                     "first_name": "אבי",
        //                     "last_name": "מרדכי",
        //                     "language_code": "he",
        //                     "user_status": "recently"
        //                 },
        //                 "chat": {
        //                     "id": -1001451090121,
        //                     "title": "סרטים שירים סדרות",
        //                     "username": "SeretPlus",
        //                     "type": "supergroup"
        //                 },
        //                 "date": 1662225929,
        //                 "text": "!גון וויק 4"
        //             },
        //             "text": "גון וויק 4\nתוצאות 20-30\nמתוך",
        //             "reply_markup": {
        //                 "inline_keyboard": [
        //                     [
        //                         {
        //                             "text": "כל הסדרות בחיפוש גון וויק 2 ק ת 2017 1080p BluRay  mp4",
        //                             "callback_data": "12781732"
        //                         }
        //                     ],
        //                     [
        //                         {
        //                             "text": "כל הסדרות בחיפוש גון וויק 3 ק ת 2019 1080p BluRay  mp4",
        //                             "callback_data": "12781733"
        //                         }
        //                     ],
        //                     [
        //                         {
        //                             "text": "⏪10",
        //                             "callback_data": "12781734"
        //                         },
        //                         {
        //                             "text": "10⏩",
        //                             "callback_data": "12781735"
        //                         }
        //                     ],
        //                     [
        //                         {
        //                             "text": "מחק הודעה",
        //                             "callback_data": "12781736"
        //                         }
        //                     ]
        //                 ]
        //             }
        //         },
        //         "chat_instance": "-5686476100341976119",
        //         "data": "12781735"
        //     }
        // }
        // END
        // );
        // $this->channel_message = new Update($this->config, json_encode([]));
        // $this->new_member = new Update($this->config, json_encode([]));
        // $this->document_file = new Update($this->config, json_encode([]));
        // $this->photo_file = new Update($this->config, json_encode([]));
        // $this->music_file = new Update($this->config, json_encode([]));
        // $this->sticker_file = new Update($this->config, json_encode([]));
    }
}

// class check
// {
//     use UpdateTypes;

//     public function test()
//     {
//         $this->init();
//         var_dump($this->private_message);
//     }
// }

// $check = new check;
// $check->test();
