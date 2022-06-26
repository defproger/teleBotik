<?php
include_once "error_log.php";
require_once "app/inc.php";


$bot = new Bot('5559490398:AAEqGo_5HPI5DiHyRZYCrHONP6fxRanYsK4');
$bot->startLog();

//$bot->toChat('1005024016')
//    ->Message('Hello')
//    ->replyMarkup(
////        Msg::Inline(
////            Msg::Row(
////                Msg::dataBtn('Hello', 'hellobtn'), Msg::dataBtn('fuckOff', 'fuckOff')
////            ),
////            Msg::Row(
////                Msg::dataBtn('Last', 'hi')
////            )
////        )
//        Msg::Keyboard(
//            Msg::Row(
//                Msg::Key('Приветь'), Msg::Key('Приветь2')
//            )
//        )
////        Msg::getContact()
//    )
//    ->Send();

$bot->getUpdate(
    function ($text) use ($bot) {
        switch ($text) {
            case '/start':
                $bot->Message('Привет, вот функционал')
                    ->replyMarkup(
                        Msg::Inline(
                            Msg::Row(
                                Msg::dataBtn('кнопочки', 'hellobtn'), Msg::dataBtn('иди нахуй', 'fuckOff')
                            ),
                            Msg::Row(
                                Msg::dataBtn('верификация', 'contact')
                            )
                        )
                    )
                    ->Send();
                break;
        }
    },
    function ($data) use ($bot) {
        switch ($data) {
            case 'hellobtn':
                $bot->Message('Снизу появились кнопочки')
                    ->replyMarkup(
                        Msg::Keyboard(
                            Msg::Row(
                                Msg::Key('Приветь'), Msg::Key('Приветь2')
                            )
                        )
                    )
                    ->Send();
                break;
            case 'fuckOff':
                $bot->Message('Сам иди нахуй')
                    ->Send();
                break;
            case 'contact':
                $bot->Message('Для верификации отправьте контакт')
                    ->replyMarkup(
                        Msg::getContact()
                    )
                    ->Send();
                break;
        }
    }
);

$bot->endLog();
//        if (isset($update['message']['contact'])) {


