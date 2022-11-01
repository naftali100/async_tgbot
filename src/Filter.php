<?php

namespace bot_lib;

use Respect\Validation\Validator as v;

class Filter
{
    /// update type filters

    public static function update($types, $not = false)
    {
        return self::Filter('updateType', $types, $not);
    }
    public static function messageUpdates($not = false)
    {
        $validator = v::attribute('updateType', v::equals('message'));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }
    public static function cbqUpdates($not = false)
    {
        $validator = v::attribute('updateType', v::equals('callback_query'));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }
    public static function inlineUpdates($not = false)
    {
        $validator = v::attribute('updateType', v::equals('inline_query'));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }
    public static function serviceUpdates($not = false)
    {
        $validator = v::attribute('service', v::trueVal());
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }
    public static function fileUpdates($not = false)
    {
        $validator = v::attribute('media', v::not(v::nullType()));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }

    public static function webAppUpdates($not = false)
    {
        $validator = v::attribute('web_app_data', v::not(v::nullType()));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }

    /// filter specific update parts

    public static function message($text, $not = false)
    {
        return v::allOf(
            self::Filter('text', $text, $not),
            self::MessageUpdates()
        );
    }
    public static function messageRegex($text, $not = false)
    {
        $validator = v::attribute('text', v::regex($text));
        if ($not) {
            $validator = v::not($validator);
        }

        return v::allOf(
            $validator,
            self::MessageUpdates()
        );
    }
    public static function cbq($data, $not = false)
    {
        return v::allOf(
            self::Filter('data', $data, $not),
            self::CbqUpdates()
        );
    }
    public static function webData($data, $not = false)
    {
        return v::allOf(
            self::Filter('web_app_data.data', $data, $not),
            self::webAppUpdates()
        );
    }
    public static function user($id, $not = false)
    {
        return self::Filter('from.id', $id, $not);
    }
    public static function chat($id, $not = false)
    {
        return self::Filter('chat.id', $id, $not);
    }

    /// type of 

    public static function fileType($type, $not = false)
    {
        return self::Filter('media.file_type', $type, $not);
    }

    /// chat type 

    public static function chatType($type, $not = false)
    {
        return self::Filter('chatType', $type, $not);
    }

    public static function privateChat($not = false)
    {
        return self::ChatType('private', $not);
    }
    public static function groupChat($not = false)
    {
        return self::ChatType(['group', 'supergroup'], $not);
    }
    public static function channelChat($not = false)
    {
        return self::ChatType('channel', $not);
    }

    // public static function ServiceType($type, $not = false)

    /// misc

    public static function joinRequests($not = false)
    {
        $validator = v::keyNested('chat_join_request');
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }
    // public static function NewMember()
    // {
    // }
    // public static function MyStatusChanged()
    // {
    // }

    // free form - exact math
    public static function filter($getter, $filter, $not)
    {
        if (gettype($filter) != 'array') {
            $filter = [$filter];
        }
        $validator = v::keyNested($getter, v::in($filter));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }
    public static function startsWith($getter, $filter, $not = false)
    {
        $validator = v::keyNested($getter, v::startsWith($filter));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }
}
