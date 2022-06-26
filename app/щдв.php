<?php

require_once "../config.dist.php";
require_once "inc.php";
require_once "../telegram/db/db_function.php";
require_once '../telegram/onu/OLT.php';

//sendMassage([
//    'text' => '',
//
//    'inline_keyboard' => null,
//    'buttonCountLine' => 2,
//    'inlineData' => null,
//
//    'bottomKeyboard' => null,
//    'contact' => null,  //для использования true
//
//    'chat' => 919191,
//
//    'parse' => HTML,
//
//    'API_TOKEN' => '1286080556:AAGTH_qNEvR5gNh_1ZtSiQNMnK8lL43sk1Q' // токен для отправки в другого бота
//]);
//------------------------
// <editor-fold defaultstate="collapsed" desc="parse tg text">
//$parsetext = '
//*bold \*text*
//_italic \*text_
//__underline__
//~strikethrough~
//||spoiler||
//*bold _italic bold ~italic bold strikethrough ||italic bold strikethrough spoiler||~ __underline italic bold___ bold*
//[inline URL](http://www.example.com/)
//[inline mention of a user](tg://user?id=123456789)
//`inline fixed-width code`
//```
//pre-formatted fixed-width code block
//```
//```python
//pre-formatted fixed-width code block written in the Python programming language
//```
//';
//sendMassage([
//            'text' => $parsetext,
//            'parse' => 'MarkdownV2'
//        ]);
// </editor-fold>
function sendMassage($replyData)
{

    global $update, $botAPI, $queryType;


    $text = is_array($replyData) ? $replyData['text'] : $replyData;
    $inline_keyboard = isset($replyData['inline_keyboard']) ? $replyData['inline_keyboard'] : null;
    $buttonCountLine = isset($replyData['buttonCountLine']) ? $replyData['buttonCountLine'] : 2;
    $inlinedata = isset($replyData['inlineData']) ? $replyData['inlineData'] : null;

    $bottom_keyboard = isset($replyData['bottomKeyboard']) ? $replyData['bottomKeyboard'] : null;
    $contact = isset($replyData['contact']) ? true : null;
    $bottomButtonCountLine = !isset($replyData['buttonCountLine']) ? 1 : $buttonCountLine;

    $chat = isset($replyData['chat']) ? $replyData['chat'] : $update[$queryType]['from']['id'];

    $parse = isset($replyData['parse']) ? $replyData['parse'] : null;

    $bot = isset($replyData['API_TOKEN']) ? "https://api.telegram.org/bot" . $replyData['API_TOKEN'] : $botAPI;

    $data = http_build_query([
        'chat_id' => $chat,
        'text' => $text,
        'parse_mode' => $parse
    ]);

    if ($inline_keyboard === null && $bottom_keyboard === null && !$contact) {
        LOG::W($bot . "/sendMessage?{$data}");
        return file_get_contents($bot . "/sendMessage?{$data}");
    } else {
        $keyboard = json_encode(setKeyboard($inline_keyboard, $buttonCountLine, $inlinedata, $bottom_keyboard, $contact, $bottomButtonCountLine));
        LOG::W($bot . "/sendMessage?{$data}&reply_markup={$keyboard}");
        return file_get_contents($bot . "/sendMessage?{$data}&reply_markup={$keyboard}");
    }
}

function sendPhoto($path)
{
    global $update, $botAPI, $queryType;
    $data = http_build_query([
        'chat_id' => $update[$queryType]['from']['id'],
        'photo' => $path,
    ]);
    LOG::W($botAPI . "/sendPhoto?{$data}");
    return file_get_contents($botAPI . "/sendPhoto?{$data}");
}

function deleteMassage($massage_id)
{
    global $update, $botAPI, $queryType;

    if (is_array($massage_id)) {
        foreach ($massage_id as $mid) {
            $data = http_build_query([
                'chat_id' => $update[$queryType]['from']['id'],
                'message_id' => $mid
            ]);
            file_get_contents($botAPI . "/deleteMessage?{$data}");
        }
    } else {
        $data = http_build_query([
            'chat_id' => $update[$queryType]['from']['id'],
            'message_id' => $massage_id
        ]);
        file_get_contents($botAPI . "/deleteMessage?{$data}");
    }
}


//toKeyboard(['кнопка 1','кнопка 2']);
//toKeyboard([
//    'text' => 'РАБОТАЕТ ТОЛЬКО ДЛЯ ОДНОЙ КНОПКИ',
//    'data' => 'xzy'
//]);
//toKeyboard([
//    [
//        'text' => 'Кнопка 1',
//        'data' => 'x'
//    ],
//    [
//        'text' => 'Кнопка 2',
//        'url' => 'y'
//    ]
//]);
function toKeyboard($data)
{
    $q = [];
    if (isset($data['text'])) {
        if (isset($data['url'])) {
            $q['inlineMenu'][0] = [
                'text' => $data['text'],
                'data' => $data['url'],
                'type' => 'url'
            ];
            $q[0] = $data['url'];
        } else {
            $q['inlineMenu'][0] = [
                'text' => $data['text'],
                'data' => $data['data'],
                'type' => 'data'
            ];
            $q[0] = $data['data'];
        }

    } elseif (isset($data[0]['text'])) {
        $i = 0;
        foreach ($data as $datum) {
            if (isset($datum['url'])) {
                $q['inlineMenu'][$i] = [
                    'text' => $datum['text'],
                    'data' => $datum['url'],
                    'type' => 'url'
                ];
                $q[$i] = $datum['url'];
            } else {
                $q['inlineMenu'][$i] = [
                    'text' => $datum['text'],
                    'data' => $datum['data'],
                    'type' => 'data'

                ];
                $q[$i] = $datum['data'];
            }
            $i++;

        }
    } else {
        $i = 0;
        foreach ($data as $datum) {
            if (is_array($datum)) {
                $text = implode(' ', $datum);
            } else {
                $text = $datum;
            }
            $datum = json_encode($datum);


            $q['inlineMenu'][$i] = [
                'text' => $text,
                'data' => $datum,
                'type' => 'data'
            ];
            $q[$i] = $datum;
            $i++;
        }
    }
    return $q;
}

function fixNumber($number)
{
    preg_replace(' ', '', $number);
    $number = strlen($number) == 13 ? ltrim($number, '+380') : $number;
    $number = strlen($number) == 12 ? ltrim($number, '380') : $number;
    $number = strlen($number) == 10 ? ltrim($number, '0') : $number;
    return $number;
}

function implode_key($glue, $arr, $key)
{
    $arr2 = array();
    foreach ($arr as $f) {
        if (!isset($f[$key])) continue;
        $arr2[] = $f[$key];
    }
    return implode($glue, $arr2);
}

//seeUser ([
//    'street-name' => 'Галинина',
//    'street-num' => '142',
//    'flat' => '31',
//
//    'mac' => '123456789012',
//    'number' => '0939339933',
//
//    'did' => 1,
//    'uid' => 5342,
//]);
function seeUser($arr)
{
    global $tgUserstable, $user_id;

    $sql = "select users.id_user,users.first_n,users.last_n,users.street_id,users.house,users.flat from users where code not in (132) and";

    if (isset($arr['street-name'])) {
        $sql .= " street_id in ( select id from street where name like '%{$arr['street-name']}%')";
        if (isset($arr['street-num'])) $sql .= " and users.house={$arr['street-num']}";
        if (isset($arr['flat'])) $sql .= " and users.flat={$arr['flat']}";
    } elseif (isset($arr['mac'])) {
        $sql .= " id_user = (select uid from onu_id where mac='{$arr['mac']}')";
    } elseif (isset($arr['sn'])) {
        $sql .= " id_user = (select uid from onu_id where sn='{$arr['sn']}')";
    } elseif (isset($arr['did'])) {
        $sql .= " dep={$arr['did']} and uid={$arr['uid']}";
    }

    $sql .= " limit 12";

    LOG::SQL("Запрос на поиск пользователей | {$sql}");

    $users = db_query($sql);
    if (isset($users[0]) && !isset($users[1])) {
        getUserInfo($users[0]['id_user']);
        $deleteDiagnostickMessage = getDiagnostick($users[0]['id_user']);
        db_update($tgUserstable, $user_id, ['delete_massage_id' => json_encode($deleteDiagnostickMessage)]);
    } else if (isset($users[0])) {

        $button = [];
        $queryToDB = [];
        $i = 0;

        foreach ($users as $user) {
            $street = db_query("select prefix,name from street where id={$user['street_id']}");

            $queryToDB['search_' . $i] = [
                'uid' => $user['id_user'],
                'street_id' => $user['street_id']
            ];

// TODO Если больше 10 вариантов - уточнить номер квартиры
            $button['inlineMenu'][$i] = [
                'text' => $user['last_n'] . ' ' . $user['first_n'] . ' ' . $street[0]['prefix'] . ' ' . $street[0]['name'] . ' ' . $user['house'] . ' кв.' . $user['flat'],
                'data' => 'search_' . $i
            ];
            $button[$i] = 'search_' . $i;
            $i++;
        }
        db_update($tgUserstable, $user_id, [
            'search_query' => json_encode($queryToDB)
        ]);

        sendMassage([
            'text' => "Кого именно вы ищите?",
            'inline_keyboard' => $button,
            'buttonCountLine' => 1,
            'inlineData' => 1,
        ]);
    } else {
        sendMassage('Не найдено.');
    }

    if (isset($users[11])) {
        sendMassage('Найдено больше 10 пользователей, что бы увидеть более подробную информацию - уточните вопрос.');
    }
}

function getUserInfo($uid)
{

    $info = db_query('select * from users where users.id_user=' . $uid);
    $numbers = [
        'tel_h' => $info[0]['tel_h'],
        'tel_w' => $info[0]['tel_w'],
        'tel_m' => $info[0]['tel_m'],
    ];
    foreach ($numbers as $number) {
        if ($number != '') $nums[] = $number;
    }
    $street_info = db_query("select prefix,name from street where id=(select street_id from users where id_user={$uid})");
    $balance = db_query('select balance from balance where uid=' . $uid);
    $inet_status = db_query("select enable_internet from priveleges where id_user = {$info[0]['uid']}");
    $inet_status = $inet_status[0]['enable_internet'] == 1 ? '🟢' : '🔴';
    $tariff = db_query("select name from price where code = {$info[0]['code']} ");
    $tags = db_query("select name from tags where tag_id in (select tag_id from users_tag where uid = {$uid})");
    sendMassage([
        'text' => "
{$inet_status} {$info[0]['first_n']} {$info[0]['last_n']} {$info[0]['otch_n']}
📑 {$info[0]['dep']}.{$info[0]['uid']}
🏠 {$street_info[0]['prefix']} {$street_info[0]['name']} {$info[0]['house']} кв. {$info[0]['flat']} Этаж {$info[0]['floor']}
☎ " . implode(',', $nums) . "
💰 {$balance[0]['balance']} грн
📦 {$tariff[0]['name']}
🏷 " . implode_key(',', $tags, 'name') . "

ip: {$info[0]['ip_addr']}
user mac: {$info[0]['mac_addr']}"]);
}

function getDiagnostick($uid, $update = null)
{
    global $domen;
// <editor-fold defaultstate="collapsed" desc="Вывод динамических данных ONU">
    $q = "select * from onu_olt,onu_id where onu_olt.onu_id = onu_id.id and onu_id.uid={$uid}";
    LOG::SQL("Вывод онушки | {$q}");
    $onus = db_query($q);
    if (!isset($onus[0])) {
        return sendMassage('Нет onu для этого абонента');
    } else {
        foreach ($onus as $onu) {
            $olt = db_getById('olt', $onu['olt_id']);
            $date = new DateTime();
            $date->modify('-1 day');
            $data = http_build_query([
                'id' => $onu['onu_id'],
                //                'date_from' => $date->format('Y-m-d')
            ]);
            if ($update !== null) {
                $onushki = new OLT($olt['ip'], $olt['community'], $onu['interface']);

                $onuInfo = $onushki->getOnuInfo();
                $onuInfo = $onuInfo[0];
                $onuStats = $onushki->getOnuStatistic();
                $onuStats = $onuStats[0];

                $arr[] = sendMassage(['text' => "
    OLT: {$olt['name']}

    {$onu['interface_name']}

    mac: {$onuInfo['mac']}
    sn: {$onuInfo['sn']}

    signal: {$onuStats['signal']}
    reverse signal: {$onuStats['reverse_signal']}

    distance: {$onuInfo['distance']}

    model: {$onuInfo['model']}
    статус: {$onuInfo['state']}

    описание: {$onuInfo['descr']}

    Время регистрации: {$onuInfo['timeregister']}
    auth pass: {$onuInfo['timeauthpass']}
    deregister: {$onuInfo['timederegister']}

    обновленно: " . date('Y-m-d H:i:s'),
                    'inline_keyboard' => toKeyboard([
                        [
                            'text' => 'traffic',
                            'url' => "https://{$domen}/telegram/charts.php?{$data}"
                        ],
                        [
                            'text' => 'обновить',
                            'data' => 'reload_' . $uid
                        ],
                    ]),
                    'inlineData' => true,
                ]);

            } else {
                $stats = db_query("select * from onu_stats where onu_id = {$onu['onu_id']} order by id desc limit 1");
                $stats = $stats[0];

                $arr[] = sendMassage(['text' => "
    OLT: {$olt['name']}\n
    {$onu['interface_name']}\n
    mac: {$onu['mac']}\n
    sn: {$onu['sn']}\n
    signal: {$stats['signal']}
    reverse signal: {$stats['reverse_signal']}\n
    distance: {$onu['distance']}\n
    model: {$onu['model']}
    статус: {$onu['status']}\n
    описание: {$onu['description']}\n
    Время регистрации: {$onu['timeregister']}
    auth pass: {$onu['timeauthpass']}
    deregister: {$onu['timederegister']} \n
    обновленно: {$onu['date_update']}
    ",
                    'inline_keyboard' => toKeyboard([
                        [
                            'text' => 'traffic',
                            'url' => "https://{$domen}/telegram/charts.php?{$data}"
                        ],
                        [
                            'text' => 'обновить',
                            'data' => 'reload_' . $uid
                        ],
                    ]),
                    'inlineData' => true]);
            }
        }
        foreach ($arr as $ar) {
            $ar = json_decode($ar, true);
            $ar = $ar['result']['message_id'];
            $r[] = $ar;
        }
        return $r;
    }
    //    ["{\"ok\":true,\"result\":{\"message_id\":8630,\"from\":{\"id\":1286080556,\"is_bot\":true,\"first_name\":\"leva loh\",\"username\":\"levalohbot\"},\"chat\":{\"id\":1005024016,\"first_name\":\"\\u1d053\\ua730\\u1d00\\u1d1c\\u029f\\u1d1b\",\"last_name\":\"\\u1d18\\u0280\\u1d0f\\u0262\\u0280\\u1d00\\u1d0d\\u1d0d\\u1d07\\u0280\",\"username\":\"defProger\",\"type\":\"private\"},\"date\":1638584580,\"text\":\"OLT: \\u0414\\u041d8 (243)\\n\\nepon-onu_1\/2\/7:20\\n\\nmac: d4:25:cc:0a:3b:6c\\n\\nsignal: -18.33\\nreverse signal: -21.31\\n\\ndistance: 1185\\n\\nmodel: ZTE-F420\\n\\u0441\\u0442\\u0430\\u0442\\u0443\\u0441: on-line\\n\\n\\u043e\\u043f\\u0438\\u0441\\u0430\\u043d\\u0438\\u0435: \\n\\n\\u0412\\u0440\\u0435\\u043c\\u044f \\u0440\\u0435\\u0433\\u0438\\u0441\\u0442\\u0440\\u0430\\u0446\\u0438\\u0438: 2021-11-23 16:23:30\\nauth pass: 2021-11-23 16:23:30\\nderegister: 2021-11-23 09:54:48 \\n\\n\\u0432\\u0445\\u043e\\u0434\\u044f\\u0449\\u0438\\u0439 \\u0442\\u0440\\u0430\\u0444\\u0438\\u043a: 1488793585459\\n\\u0438\\u0441\\u0445\\u043e\\u0434\\u044f\\u0449\\u0438\\u0439 \\u0442\\u0440\\u0430\\u0444\\u0438\\u043a: 169754459499 \\n\\n\\u043e\\u0431\\u043d\\u043e\\u0432\\u043b\\u0435\\u043d\\u043d\\u043e: 2021-12-04 04:01:19\"}}"]
    // </editor-fold>
}

/////////////////////////////////////////////////
/////////////////////////////////////////////////
//////////////system functions///////////////////
/////////////////////////////////////////////////
/////////////////////////////////////////////////
// <editor-fold defaultstate="collapsed" desc="set keyboard">
function setKeyboard($buttonName, $buttonCountLine = null, $data = null, $bottomKeyboard = null, $contact = null, $bottomButtonCountLine = null)
{
    global $command;

    $data = $data == null ? $command : $buttonName;

    $buttonCountLine = $buttonCountLine == null ? 2 : $buttonCountLine;
    $bottomButtonCountLine = $bottomButtonCountLine == null ? 1 : $bottomButtonCountLine;

    // <editor-fold defaultstate="collapsed" desc="плавающие кнопки">
    if (is_array($buttonName)) {
        $buttonInLine = 0;
        $adsBtn = [];
        $i = 0;
        foreach ($buttonName as $btn) {
            foreach ($data['inlineMenu'] as $inlineMenu) {
                if ($btn == $inlineMenu["data"]) {
                    if ($buttonInLine < $buttonCountLine) {
                        if ($inlineMenu['type'] == 'data') {
                            $adsBtn[$i][] = [
                                "text" => $inlineMenu['text'],
                                "callback_data" => $inlineMenu['data']
                            ];
                        } elseif ($inlineMenu['type'] == 'url') {
                            $adsBtn[$i][] = [
                                "text" => $inlineMenu['text'],
                                "url" => $inlineMenu['data']
                            ];
                        }
                        $buttonInLine++;
                    } else {
                        $i++;
                        $buttonInLine = 1;

                        if ($inlineMenu['type'] == 'data') {
                            $adsBtn[$i][] = [
                                "text" => $inlineMenu['text'],
                                "callback_data" => $inlineMenu['data']
                            ];
                        } elseif ($inlineMenu['type'] == 'url') {
                            $adsBtn[$i][] = [
                                "text" => $inlineMenu['text'],
                                "url" => $inlineMenu['data']
                            ];
                        }
                    }


                }
            }
        }
        $inline_keyboard =
            $adsBtn;
    } else {
        foreach ($data['inlineMenu'] as $inlineMenu) {
            if ($buttonName == $inlineMenu["data"]) {
                if ($inlineMenu['type'] == 'data') {
                    $inline_keyboard = [
                        [
                            [
                                "text" => $inlineMenu['text'],
                                "callback_data" => $inlineMenu['data']
                            ]
                        ]
                    ];
                } elseif ($inlineMenu['type'] == 'url') {
                    $inline_keyboard = [
                        [
                            [
                                "text" => $inlineMenu['text'],
                                "url" => $inlineMenu['data']
                            ]
                        ]
                    ];
                }

            }
        }
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="клавиатурные кнопки">


    if ($bottomKeyboard !== null) {
        $bottom_keyboard = [];
        $buttonInLine = 0;
        $i = 0;
        if (is_array($bottomKeyboard)) {
            foreach ($bottomKeyboard as $btn) {
                if ($buttonInLine < $bottomButtonCountLine) {
                    $bottom_keyboard[$i][] = [
                        'text' => $btn
                    ];
                    $buttonInLine++;
                } else {
                    $buttonInLine = 1;
                    $i++;

                    $bottom_keyboard[$i][] = [
                        'text' => $btn
                    ];
                }
            }
        } else {
            $bottom_keyboard = [[
                [
                    'text' => $bottomKeyboard
                ]
            ]];
        }
    } elseif ($contact) {
        $bottom_keyboard = [[
            [
                'text' => 'Передать контакт',
                'request_contact' => true
            ]
        ]];
    }
    // </editor-fold>

    if ($bottomKeyboard === null && !$contact) {
        //        logs("Кнопки \n" . print_r($inline_keyboard, 1));
        return [
            "inline_keyboard" => $inline_keyboard
        ];

    } else {
        //        logs("Клавиатура \n" . print_r($bottom_keyboard, 1));
        return [
            'resize_keyboard' => true,
            'keyboard' => $bottom_keyboard
        ];

    }


}

// <?// </editor-fold/editor-fold>

function getmicrotime()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

//function errorHandler($errno, $errstr, $errfile, $errline)
//{
//    global $errors;
//    $errors = 'An error occured. ' . $errstr . ' at ' . $errfile . ':' . $errline;
//    echo $errors;
//
//    $f = fopen('./.error_log', 'a+');
//    fputs($f, date('Y-m-d H:i:s') . ' ' . $errors . "\n");
//    fclose($f);
//}
//set_error_handler('errorHandler');
