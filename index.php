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
                $bot->Message('И тебе привет, отправь номер')
                    ->replyMarkup(
                        Msg::getContact()
                    )
                    ->Send();
                break;
        }
    },
    function ($data) use ($bot) {

    }
);

$bot->endLog();

exit();
//$command = [
//    "inlineMenu" => [
//        'reload' => [
//            "data" => "reload",
//            "text" => "обновить",
//            'type' => 'data'
//        ],
//    ],
//    "cmd" => [
//        "info" => "Смотреть информацию",
//        "reg" => "Регистрировать информацию",
//    ]
//];
////все нажатия на кнопки
//if (isset($update['callback_query'])) {
//    include_once "components/callback_data.php";
//}
////при тексте
//$queryType = 'message';
//$msg = $update['message']['text'];
//$user_id = intval($update['message']['chat']['id']);
//
////$f = fopen('./.history', 'a+');
////fputs($f, date('Y-m-d H:i:s') . " | user: {$user_id} | massage: {$msg} |\n-------------------------\n");
////fclose($f);
//
//switch ($msg) {
//    case 'q':
//        break;
//    case '/start':
//        sendMassage([
//            'text' => "Выбери нужную функцию в меню снизу",
//            'buttonCountLine' => 2,
//            'bottomKeyboard' => [$command['cmd']['info'], $command['cmd']['reg']]
//        ]);
//        break;
//    case $command["cmd"]['info']:
//
//        sendMassage($searchParametresInfo);
//        db_update($tgUserstable, $user_id, ['type' => 'search']);
//
//        break;
//
//    default:
//
//        if (isset($update['message']['contact'])) {
//            sendMassage([
//                'text' => "Запрос на авторизацию\n" . $nic['login'] . ' ' . $update['message']['contact']['phone_number'],
//                'chat' => $admin,
//                'API_TOKEN' => $botToken, // токен nyana_bot
//                'inline_keyboard' => toKeyboard(['text' => 'Разрешить', 'data' => 'addUser_' . $user_id . '_' . $admin]),
//                'inlineData' => 1,
//            ]);
//        }
//
//        break;
//}

