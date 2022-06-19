<?php

class Msg
{
    // inline keyboard
    public static function Inline(...$rows)
    {
        return [
            "inline_keyboard" => $rows
        ];
    }

    public static function Row(...$buttons)
    {
        return $buttons;
    }

    public static function dataBtn($text, $data)
    {
        return [
            "text" => $text,
            "callback_data" => $data
        ];
    }

    public static function urlBtn($text, $url)
    {
        return [
            "text" => $text,
            "url" => $url
        ];
    }

    // keyboard
    public static function Keyboard(...$rows)
    {
        return [
            'resize_keyboard' => true,
            'keyboard' => $rows
        ];
    }

    public  static function Key ($text) {
        return [
            "text" => $text
        ];
    }


    // for contact only
    public static function getContact($text = null)
    {
        $text = $text === null ? 'Передать контакт' : $text;
        return [
            'resize_keyboard' => true,
            'keyboard' => [
                [
                    [
                        'text' => $text,
                        'request_contact' => true
                    ]
                ]
            ]
        ];
    }
}
