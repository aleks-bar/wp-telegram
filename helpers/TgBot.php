<?php

class TgBot
{
    private static TgBot $instance;
    private static array $bots_for_chat_group = [];


    public function getBotsForChatGroup(): array
    {
        return self::$bots_for_chat_group;
    }

    public function addChatGroupBot(string $bot_token, array|string $chats = ''): void
    {
        $current_chats = is_string($chats) ? [$chats] : $chats;

        if (empty(self::$bots_for_chat_group[$bot_token])) {
            self::$bots_for_chat_group[$bot_token] = $current_chats;
        } else {
            self::$bots_for_chat_group[$bot_token] = array_merge(self::$bots_for_chat_group[$bot_token], $current_chats);
        }
    }

    public static function getInstance(): TgBot
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}