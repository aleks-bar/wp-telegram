<?php
$main_composer_json = __DIR__ . '/../composer.json';

if (file_exists($main_composer_json)) {
    $dirname = basename(__DIR__);
    $json = json_decode(file_get_contents($main_composer_json), true);

    if (empty($json['extra'])) {
        $json['extra'] = [];
    }

    $json['extra']['merge-plugin'] = [];
    $json['extra']['merge-plugin']['include'] = ["$dirname/composer.json"];

    file_put_contents($main_composer_json, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    echo "главный composer.json обновлён\n";
} else {
    echo "главный composer.json не найден\n";
}
