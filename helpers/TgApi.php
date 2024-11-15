<?php
use WeStacks\TeleBot\TeleBot;

class TgApi
{
    private static TgApi $instance;
    private TgConfig $config;
    private TgLogger $logger;
    private TgFileManager $file_manager;
    private TgBot $bot;
    private static bool $use_static_chats = true;

    private string $name = 'telegram';
    private string $version = 'v1';
    private static array $routs = [];

    public static function getInstance(): TgApi
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->config = TgConfig::getInstance();
        $this->logger = TgLogger::getInstance();
        $this->file_manager = TgFileManager::getInstance();
        $this->bot = TgBot::getInstance();
    }

    public function registerRout($url, $methods, $callback, $args = [], $permission_callback = '__return_true'): void
    {
        self::$routs[] = [
            'url' => $url,
            'settings' => [
                'methods' => $methods,
                'callback' => $callback,
                'args' => $args,
                'permission_callback' => $permission_callback,
            ]
        ];
    }
    public function getMessage($fields_data): string
    {
        $msg = '';
        foreach ($fields_data as $name => $param) {
            if ($name !== 'chspel' && $name !== 'tg_chats' && $name !== 'photos' && $name !== 'files' && $name !== 'nonce') {
                if (!empty($param)) {
                    $msg .= "$name:  " . $param . "\n";
                }
            }
        }
        return $msg;
    }

    public function init(): void
    {
        add_action('rest_api_init', function () {
            $namespace = "$this->name/$this->version";
            if (self::$routs) {
                foreach (self::$routs as $rout) {
                    register_rest_route($namespace, $rout['url'], $rout['settings']);
                }
            }
        });
    }

    public function deleteMediaFunction(WP_REST_Request $request): array
    {
        $response = [];

        if (file_exists($this->config->mediaDirectory())) {
            $response['delete_status'] = $this->file_manager->deleteFiles($this->config->mediaDirectory(), $this->logger->isUseLogs());
        }

        return $response;
    }

    public function sendToGroupFunction(WP_REST_Request $request): WP_HTTP_Response|WP_Error
    {
        $response = new WP_HTTP_Response();
        $error = new WP_Error();

        $nonce = $request->get_param('nonce');
        if (!wp_verify_nonce($nonce, 'site_nonce')) {
            return new WP_Error('invalid_nonce', 'Неверный nonce', ['status' => 403]);
        }

        $bots = $this->bot->getBotsForChatGroup();
        $tg_chats = [];
        $spam = $request->get_params()['chspel'] ?? null;
        $url_to_media = get_template_directory_uri() . '/telegram/files/media/';
        $media_urls = [];
        $media_data = [];
        $send_data = [];


        if (isset($spam) && $spam === "") {
            $parameters = $request->get_params();
            $data = [];

            if ($parameters) {
                foreach ($parameters as $name => $value) {
                    $data[$name] = sanitize_text_field($value);
                }
            }

            if (!self::$use_static_chats) {
                if (empty($data['tg_chats'])) {
                    $error->add("invalid_bot_chats", "Не указан чат(ы) для отправки", ['status' => 400]);
                } else {
                    $tg_chats = explode(",", $data['tg_chats']);
                }
            }

            $isMedia = !empty($_FILES['files']);

            if ($isMedia) {
                $this->file_manager->moveFilesToFolder($_FILES['files']);
                $media_urls = $this->file_manager->getMediaUrls($_FILES['files'], $url_to_media);
            }

            $message = $this->getMessage($data);

            if (!empty($bots)) {
                $send_data['chats'] = [];

                foreach ($bots as $bot_token => $bot_chats) {
                    try {
                        $bot = new TeleBot($bot_token);
                        $chats = self::$use_static_chats ? $bot_chats : $tg_chats;

                        foreach ($chats as $chat_id) {
                            if ($isMedia) {
                                foreach ($media_urls as $index => $media_url) {
                                    $input_media_photo = ['type' => 'photo', 'media' => $media_url];
                                    if ($index === 0) {
                                        $input_media_photo['caption'] = $message;
                                    }

                                    $media_data[] = $input_media_photo;
                                }

                                $send_data['chats'][$chat_id] = $bot->sendMediaGroup([
                                    'chat_id' => $chat_id,
                                    'media' => $media_data
                                ]);
                            } else {
                                $send_data['chats'][$chat_id] = $bot->sendMessage([
                                    'chat_id' => $chat_id,
                                    'text' => $message
                                ]);
                            }
                        }
                    } catch (Exception $e) {
                        $error->add('exception', $e->getMessage(), ['status' => 400]);
                    }
                }

                $response->set_data($send_data);
            } else {
                $error->add('invalid_token', "Отсутствует бот токен", ['status' => 400]);
            }

            if ($isMedia && $this->config->isDeleteMedia()) {
                $this->file_manager->deleteFiles($this->config->mediaDirectory(), $this->logger->isUseLogs());
            }
        } else {
            $response->set_data('spam');
        }
        return $error->has_errors() ? $error : $response;
    }
}
