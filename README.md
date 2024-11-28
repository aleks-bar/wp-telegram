# Установка через `composer`
Установку нужно выполнить в корне темы.<br>
При установке соглашаемся со всем что предлагается.
```
composer create-project aleks-bar/wp-telegram telegram
```
> [!WARNING]
> Обязательно нужно либо указать название директории после `aleks-bar/wp-telegram` либо создать её заранее и установить из нёё

# Инициализация

В файле `functions.php` сделать инициализацию. Можно указать вместо массива чатов только 1 переданный строкой `$telegramg->initSendToGroupChats(BOT_TOKEN, TG_CHAT)`
```
use TelegramClass\Telegram;
$telegramg = Telegram::getInstance();
$telegramg->initSendToGroupChats(BOT_TOKEN, [TG_CHAT1, TG_CHAT2, ...TG_CHAT(n)]);
```

# Отправка

Отправлять данные из формы нужно на урл `урл-текущего-сайта/wp-json/telegram/v1/send`
> [!WARNING]
> В форме должны быть 2 обязательных инпута <br>
> `<input type="hidden" name="chspel" value="">` <br>
> `<input type="hidden" name="nonce" value="<?= wp_create_nonce('site_nonce') ?>">`

# Особенности
1. Отправку лучше делать через FormData. <br>
2. У инпутов `name` можно делать русскими, именно их название будет прилетать в сообщение. <br>
3. Изображение нужно отправлять таким способом:
```
const formData = new FormData();
const files = form.querySelector( 'input[type="file"]' ).files
for ( let i = 0; i < files.length; i++ ) {
    formData.append( 'files[]', inptFiles[ i ] );
}
```

