<?php

namespace bot_lib;

use Respect\Validation\Validator as v;

class Filter
{
    /// update type filters

    static function Update($types, $not = false)
    {
        return self::Filter('updateType', $types, $not);
    }
    static function MessageUpdates($not = false)
    {
        $validator = v::attribute('updateType', v::equals('message'));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }
    static function CbqUpdates($not = false)
    {
        $validator = v::attribute('updateType', v::equals('callback_query'));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }
    static function InlineUpdates($not = false)
    {
        $validator = v::attribute('updateType', v::equals('inline_query'));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }
    static function ServiceUpdates($not = false)
    {
        $validator = v::attribute('service', v::trueVal());
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }
    static function FileUpdates($not = false)
    {
        $validator = v::attribute('media', v::not(v::nullType()));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }

    /// filter specific update parts

    static function Message($text, $not = false)
    {
        return v::allOf(
            self::Filter('text', $text, $not),
            self::MessageUpdates()
        );
    }
    static function MessageRegex($text, $not = false)
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
    static function Cbq($data, $not = false)
    {
        return v::allOf(
            self::Filter('data', $data, $not),
            self::CbqUpdates()
        );
    }
    static function User($id, $not = false)
    {
        return self::Filter('from.id', $id, $not);
    }
    static function Chat($id, $not = false)
    {
        return self::Filter('chat.id', $id, $not);
    }

    /// type of 

    static function FileType($type, $not = false)
    {
        return self::Filter('media.file_type', $type, $not);
    }

    /// chat type 

    static function chatType($type, $not = false)
    {
        return self::Filter('chatType', $type, $not);
    }

    static function privateChat($not = false)
    {
        return self::ChatType('private', $not);
    }
    static function groupChat($not = false)
    {
        return self::ChatType(['group', 'supergroup'], $not);
    }
    static function channelChat($not = false)
    {
        return self::ChatType('channel', $not);
    }

    // static function ServiceType($type, $not = false)

    /// misc

    static function JoinRequests($not = false)
    {
        $validator = v::keyNested('chat_join_request');
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }
    // static function NewMember()
    // {
    // }
    // static function MyStatusChanged()
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
    static function startsWith($getter, $filter, $not = false)
    {
        $validator = v::keyNested($getter, v::startsWith($filter));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }
}
