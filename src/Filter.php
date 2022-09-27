<?php

namespace bot_lib;

use Respect\Validation\Validator as v;

class Filter
{
    /// update type filters

    static function Update($types, $not = false)
    {
        if (gettype($types) != 'array') {
            $types = [$types];
        }
        $validator = v::attribute('updateType', v::in($types));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
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
        if (gettype($text) != 'array') {
            $text = [$text];
        }
        $validator = v::attribute('text', v::in($text));
        if ($not) {
            $validator = v::not($validator);
        }
        return v::allOf(
            $validator,
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
        if (gettype($data) != 'array') {
            $data = [$data];
        }
        $validator = v::keyNested('data', v::in($data));
        if ($not) {
            $validator = v::not($validator);
        }
        return v::allOf(
            $validator,
            self::CbqUpdates()
        );
    }
    static function User($id, $not = false)
    {
        if (gettype($id) != 'array') {
            $id = [$id];
        }
        $validator = v::keyNested('user.id', v::in($id));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }
    static function Chat($id, $not = false)
    {
        if (gettype($id) != 'array') {
            $id = [$id];
        }
        $validator = v::keyNested('chat.id', v::in($id));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }

    /// type of 

    static function FileType($type, $not = false)
    {
        if (gettype($type) != 'array') {
            $type = [$type];
        }
        $validator = v::keyNested('media.file_type', v::in($type));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }
    static function ServiceType($type, $not = false)
    {
    }
    static function ChatType($type, $not = false)
    {
        if (gettype($type) != 'array') {
            $type = [$type];
        }
        $validator = v::keyNested('chatType', v::in($type));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }

    // free form 
    static function Filter($getter, $filter, $not)
    {
        if (gettype($filter) != 'array') {
            $filter = [$filter];
        }
        // TODO: is require the attribute to be in update object without getter - find workaround
        $validator = v::keyNested($getter, v::in($filter));
        if ($not) {
            $validator = v::not($validator);
        }
        return $validator;
    }
}
