<?php

namespace TelegramClass;

use TgApi;
use TgBot;

class Telegram
{
    private static Telegram $instance;

    private TgApi $api;
    private TgBot $bot;

    public static function getInstance(): Telegram
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->api = TgApi::getInstance();
        $this->bot = TgBot::getInstance();
    }

    public function initSendToGroupChats(string $token, array $chats = []): void
    {
        $this->bot->addChatGroupBot($token, $chats);

        $this->api->registerRout(
            'delete-media',
            'GET',
            [$this->api, 'deleteMediaFunction']
        );

        $this->api->registerRout(
            'send',
            'POST',
            [$this->api, 'sendToGroupFunction']
        );

        $this->api->init();
    }
}
