<?php

namespace bot_lib;

class Helpers
{
    // build inline keyboard from array
    // argument is array(/*row 1*/ array( 'text' => 'data', 'text2' => 'data2'), /*row 2*/ array( 'text3' => 'data3', 'text4' => 'data4') )
    // by default the button type is callback_data, you can also set button to url button by array(array( 'link button' => array('url' => 'link'), 'callback button' => 'data'))
    public static function keyboard($data)
    {
        $keyCol = array();
        $keyRow = array();

        foreach ($data as $row) {
            foreach ($row as $key => $value) {

                if (gettype($value) == 'array') {
                    $k = key($value);
                    $keyCol[] = array(
                        'text' => $key,
                        $k => $value[$k]
                    );
                } else {
                    $keyCol[] = array(
                        'text' => $key,
                        'callback_data' => $value
                    );
                }
            }

            $keyRow[] = $keyCol;
            $keyCol = array();
        }

        return json_encode(array('inline_keyboard' => $keyRow));
    }

    /**
     * create ChatPermissions json. 
     * 
     * accepts:
     * - block | default : block all user permissions 
     * - open : open all user permissions
     */
    public static function permissions($mode = 'block'){
        switch($mode){
            case 'block':
                return self::build_prem();
                break;
            case 'default':
                //TODO: get chat permissions
                break;
            case 'open':
                return self::build_prem(1, 1, 1, 1, 1, 1, 1, 1);
        }
    }

    public static function build_prem($send_message = false, $send_media = false, $send_polls = false,
     $send_other_messages = false, $send_web_page = false, $change_info = false, $invite = false, $pin = false
    ){
        $prem = [
            'can_send_messages' => $send_message,
            'can_send_media_messages' => $send_media,
            'can_send_polls' => $send_polls, 
            'can_send_other_messages'  => $send_other_messages, 
            'can_add_web_page_previews' => $send_web_page,
            'can_change_info' => $change_info,
            'can_invite_users' => $invite,
            'can_pin_messages' => $pin,
        ];
        return json_encode($prem); 
    }
}
