<?php

namespace bot_lib;

class Helpers
{
    // build inline keyboard from array
    // argument is array(/*row 1*/ array( 'text' => 'data', 'text2' => 'data2'), /*row 2*/ array( 'text3' => 'data3', 'text4' => 'data4') )
    // by default the button type is callback_data, you can also set button to url button by array(array( 'link button' => array('url' => 'link'), 'callback button' => 'data'))
    public static function keyboard($data, $isInline = true)
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
        $type = '';
        if($isInline){
            $type = 'inline_keyboard';
        }else{
            $type = 'keyboard';
        }

        return [$type => $keyRow];
    }

    /**
     * create ChatPermissions json. 
     * 
     * accepts:
     * - block : block all user permissions 
     * - only_messages : open only sending messages 
     * - open : open all user permissions
     */
    public static function permissions(string $mode = 'block')
    {
        switch ($mode) {
            case 'block':
                return self::build_perm();
            case 'default':
                //TODO: get chat permissions
                break;
            case 'open':
                return self::build_perm(1, 1, 1, 1, 1, 1, 1, 1);
            case 'only_messages':
                return self::build_perm(send_message: true);
            default;
        }
    }

    public static function build_perm(
        $send_message = false,
        $send_media = false,
        $send_polls = false,
        $send_other_messages = false,
        $send_web_page = false,
        $change_info = false,
        $invite = false,
        $pin = false,
        $topics = false
    ) {
        $prem = [
            'can_send_messages' => $send_message,
            'can_send_media_messages' => $send_media,
            'can_send_polls' => $send_polls,
            'can_send_other_messages'  => $send_other_messages,
            'can_add_web_page_previews' => $send_web_page,
            'can_change_info' => $change_info,
            'can_invite_users' => $invite,
            'can_pin_messages' => $pin,
            'can_manage_topics' => $topics
        ];
        return json_encode($prem);
    }

    static function objectToArray($o)
    {
        $a = array();
        foreach ($o as $k => $v) {
            $a[$k] = (is_array($v) || is_object($v)) ? Helpers::objectToArray($v) : $v;
        }
        return $a;
    }

    // https://stackoverflow.com/a/9812059/12893054
    public static function cast($destination, $sourceObject)
    {
        if (is_string($destination)) {
            $destination = new $destination();
        }
        $sourceReflection = new \ReflectionObject($sourceObject);
        $destinationReflection = new \ReflectionObject($destination);
        $sourceProperties = $sourceReflection->getProperties();
        foreach ($sourceProperties as $sourceProperty) {
            $sourceProperty->setAccessible(true);
            $name = $sourceProperty->getName();
            $value = $sourceProperty->getValue($sourceObject);
            if ($destinationReflection->hasProperty($name)) {
                $propDest = $destinationReflection->getProperty($name);
                $propDest->setAccessible(true);
                $propDest->setValue($destination, $value);
            } else {
                $destination->$name = $value;
            }
        }
        return $destination;
    }
}
