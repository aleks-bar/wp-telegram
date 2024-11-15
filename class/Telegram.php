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

    public function initSendToGroupChats(array $bots_with_chats = []): void
    {
        foreach ($bots_with_chats as $bot) {
            $this->bot->addChatGroupBot(
                $bot['token'],
                $bot['chats']
            );
        }

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
