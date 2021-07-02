<?php

namespace bot_lib;

class Helpers
{
    // builde inline keyboard from array
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
}
