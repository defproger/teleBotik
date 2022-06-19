<?php
require_once 'Msg.php';

class Bot
{
    public $lastSendMessage = null;

    protected $chatId = null;
    protected $sendHead = null;
    protected $method = null;
    protected $replyMarkup = null;

    public function __construct($token)
    {
        $this->botApi = "https://api.telegram.org/bot{$token}/";
        $this->update = json_decode(file_get_contents('php://input'), TRUE);
    }

    //Отсыл
    public function toChat($chatId)
    {
        $this->chatId = $chatId;
        return $this;
    }

    public function Message($text, $parse = null)
    {
        $this->method = 'Message';
        $this->sendHead = http_build_query([
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => $parse
        ]);
        return $this;
    }

    public function replyMarkup($inlineOrKeyboard)
    {
        self::log('replyMarkup()', print_r($inlineOrKeyboard, 1));
        $this->replyMarkupBody = "&reply_markup=" . json_encode($inlineOrKeyboard);
        return $this;
    }

    public function Send()
    {
        if ($this->chatId !== null && $this->method !== null && $this->sendHead !== null) {
            $r = $this->botApi . "send{$this->method}?{$this->sendHead}{$this->replyMarkupBody}";
            self::log('Send()', 'Запрос ' . $r);
            $this->lastSendMessage = file_get_contents($r);
        } else {
            self::log('Send()', 'Сообщение не отправлено, что-то из переменных: chatId, methos, sendHead - равно нулю.');
        }
        $this->chatId = null;
        $this->method = null;
        $this->sendHead = null;
        $this->replyMarkupBody = null;
        self::log('Send()', 'Переменные структуры сообщения были обнулены');
    }

    public function deleteLastSendMessage()
    {

    }

    //Получение
    public function getUpdate($text, $callback_data, $else = null)
    {
        if ($this->update !== null) {
            self::log('getUpdate()', print_r($this->update));
            if ($this->update['message']['text']){
                $this->chatId = $this->update['message']['from']['id'];
                $text($this->update['message']['text']);
            }
            elseif ($this->update['callback_query']['data']){
                $this->chatId = $this->update['callback_query']['from']['id'];
                $callback_data($this->update['callback_query']['data']);
            }
            //TODO номер телефона
            $this->chatId = null;
        } else {
            if ($else !== null)
                $else();
            self::log('getUpdate()', 'Nothing from webhooks');
        }
    }

    public function getMessage()
    {

    }


    private static function log($type, $text)
    {
        if (is_array($text)) {
            $text = print_r($text, 1);
        }
        $f = fopen('.log', 'a+');
        fputs($f, date('Y-m-d H:i:s') . " | {$type} | {$text}" . "\n");
        fclose($f);
    }
}
