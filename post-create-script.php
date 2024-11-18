<?php
$parent_dir = dirname(__DIR__);
$main_composer_json = $parent_dir . '/composer.json';
$local_composer_json = __DIR__ . '/composer.json';

if (file_exists($main_composer_json) && file_exists($local_composer_json)) {
    $dirname = basename(__DIR__);
    $json = json_decode(file_get_contents($main_composer_json), true);
    $local_json = json_decode(file_get_contents($local_composer_json), true);

    if (empty($json['require'])) {
        $json['require'] = [];
    }

    if (empty($json['require']['composer/installers'])) {
        $json['require']['composer/installers'] = '^2.0';
    }

    if (empty($json['require']['westacks/telebot'])) {
        $json['require']['westacks/telebot'] = '*';
    }

    if (empty($json['require']['wikimedia/composer-merge-plugin'])) {
        $json['require']['wikimedia/composer-merge-plugin'] = '*';
    }

    if (empty($json['config'])) {
        $json['config'] = [];
    }

    if (empty($json['config']['allow-plugins'])) {
        $json['config']['allow-plugins'] = [];
    }

    if (!empty($local_json['config']['allow-plugins'])) {
        $json['config']['allow-plugins'] = array_merge($json['config']['allow-plugins'], $local_json['config']['allow-plugins']);
    }

    if (empty($json['extra'])) {
        $json['extra'] = [];
    }

    $json['extra']['merge-plugin'] = [];
    $json['extra']['merge-plugin']['include'] = ["$dirname/composer.json"];

    file_put_contents($main_composer_json, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    chdir($parent_dir);

    exec('composer update', $output, $returnCode);

    if ($returnCode === 0) {
        echo "composer update успешно выполнен\n";
    } else {
        echo "Ошибка при выполнении composer update: " . implode("\n", $output) . "\n";
    }
} else {
    echo "главный composer.json не найден\n";
}
