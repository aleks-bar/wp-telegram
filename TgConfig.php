<?php
class TgConfig
{
    private static TgConfig $instance;
    private string $media_directory;
    private string $is_delete_media;
    private string $root_path = __DIR__;

    public function __construct()
    {
        $this->is_delete_media = true;
        $this->media_directory = $this->root_path . '/files/media/';
    }

    public function isDeleteMedia(): bool
    {
        return $this->is_delete_media;
    }

    public function mediaDirectory(): string
    {
        return $this->media_directory;
    }

    public function rootPath(): string
    {
        return $this->root_path;
    }

    public static function getInstance(): TgConfig
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}