<?php

class TgFileManager
{
    private TgConfig $config;
    private TgLogger $logger;
    private static TgFileManager $instance;

    public function __construct()
    {
        $this->logger = TgLogger::getInstance();
        $this->config = TgConfig::getInstance();
    }
    public function checkFilesAvailability($array, $path): array
    {
        $response = [
            'checked' => true
        ];
        $files = [];
        foreach ($array['name'] as $index => $name) {
            $files[$index] = [];
            $files[$index]['path'] = $path . $this->getCurrentFilename($name);
            $files[$index]['available'] = file_exists($files[$index]['path']);
        }

        foreach ($files as $file) {
            if (!$file['available']) {
                $response['checked'] = false;
            }
        }

        $response['files'] = $files;

        return $response;
    }
    public function getCurrentFilename($name): array|string|null
    {
        return preg_replace('/\s+/', '_', sanitize_text_field(basename($name)));
    }

    public function deleteFiles($path, $logging = false): array
    {
        $response = [
            'is_delete' => false,
            'message' => 'directory no exist by path:'.$path
        ];

        if (file_exists($path) and is_dir($path)) {
            $dir = opendir($path);
            while (false !== ( $element = readdir($dir) )) {
                if ($element != '.' and $element != '..') {
                    $tmp = $path . $element;
                    unlink($tmp);
                    if ($this->logger->isUseDeleteLogs()) {
                        $this->logger->addDeleteFileLog($tmp);
                    }
                }
            }
            closedir($dir);

            $response['is_delete'] = true;
            $response['message'] = 'files has been delete from '.$path;
        } else {
            if ($this->logger->isUseLogs()) {
                $this->logger->addLog("delete_error: no exist path - $path");
            }
        }

        return $response;
    }
    public function getMediaForSend($files, $url_to_photo, $message = ''): ?array
    {
        if (!empty($files)) {
            $media = [];
            foreach ($files['tmp_name'] as $key => $file) {
                $media[] = ['type' => 'photo', 'media' => $url_to_photo . $this->getCurrentFilename($files['name'][$key])];
                if ($key === 0) {
                    $media[$key]['caption'] = $message;
                }
            }
            return $media;
        }
        return null;
    }
    public function getMediaUrls($files, $url_to_photo): array
    {
        $media = [];

        if (!empty($files)) {
            foreach ($files['tmp_name'] as $key => $file) {
                $media[] = $url_to_photo . $this->getCurrentFilename($files['name'][$key]);
            }
        }

        return $media;
    }

    public function moveFilesToFolder($files): void
    {
        $path = $this->config->mediaDirectory();
        if (isset($files)) {
            foreach ($files['tmp_name'] as $key => $file) {
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                $correct_file_name = $this->getCurrentFilename($files['name'][$key]);

                move_uploaded_file($files['tmp_name'][$key], $path . $correct_file_name);
            }
        }
    }

    public static function getInstance(): TgFileManager
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
