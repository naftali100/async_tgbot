<?php

namespace bot_lib;

class Filter{
    // update type filters
    static function Update($types, $not = false){}
    static function MessageUpdates($not = false){}
    static function CbqUpdates($not = false){}
    static function InlineUpdates($not = false){}
    static function ServiceUpdates($not = false){}
    static function FileUpdates($not = false){}

    // filter specific update parts
    static function Message($text, $not = false){}
    static function MessageRegex($text, $not = false){}
    static function Cbq($data, $not = false){}
    static function User($id, $not = false){}
    static function Chat($id, $not = false){}

    // type of 
    static function FileType($type, $not = false){}
    static function ServiceType($type, $not = false){}
    static function ChatType($type, $not = false){}
}