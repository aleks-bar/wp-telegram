<?php


class TgLogger
{
    private static TgLogger $instance;
    private TgConfig $config;
    private string $log_file;
    private bool $use_logs = false;
    private bool $use_delete_logs = true;

    public static function getInstance(): TgLogger
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->config = TgConfig::getInstance();
        $this->log_file = $this->config->rootPath() . '/log.txt';
    }

    public function addLog(string $log): void
    {
        $is_append = file_exists($this->log_file) && !empty(file_get_contents($this->log_file));
        if ($is_append) {
            file_put_contents($this->log_file, PHP_EOL . print_r(date('d-m-Y') . ' --- ' . $log, true), FILE_APPEND);
        } else {
            file_put_contents($this->log_file, PHP_EOL . print_r(date('d-m-Y') . ' --- ' . $log, true));
        }
    }

    public function logsEnable(): void
    {
        $this->use_logs = true;
    }

    public function logsDisable(): void
    {
        $this->use_logs = false;
    }

    public function isUseLogs(): bool
    {
        return $this->use_logs;
    }
    public function isUseDeleteLogs(): bool
    {
        return $this->use_delete_logs;
    }

    public function addDeleteFileLog($file): void
    {
        $this->addLog('delete: ' . $file);
    }
}
