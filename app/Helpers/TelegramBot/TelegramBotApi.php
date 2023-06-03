<?php

namespace App\Helpers\TelegramBot;

use Exception;
use Illuminate\Support\Facades\Log;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Message;

class TelegramBotApi
{
    /**
     * @return void
     */
    public static function GetUpdates(): void
    {
        $token = config('web3.telegram.token');
        dump("https://api.telegram.org/bot$token/getUpdates");
    }

    /**
     * @param string $text
     * @return bool
     */
    public static function SendText(string $text): bool
    {
        $token = config('web3.telegram.token');
        $chat_id = config('web3.telegram.chat_id');
        try {
            $bot = new BotApi($token);
            $m = $bot->sendMessage($chat_id, $text);
            if ($m instanceof Message) {
                return true;
            }
            return true;
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return false;
        }
    }
}
