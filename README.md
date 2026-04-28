# monolog-telegram-async
Handler for Monolog to send logs by Telegram asynchronously in HTML format

Requirements
------------

- PHP 7.4 or above
- Guzzle 7+

Installation with composer
-------------------------

```bash
composer require holdmann/monolog-telegram-async  
```

Declaring handler object
------------------------

To declare this handler, you need to know the bot token and the chat identifier(chat_id) to
which the log will be sent.

```php
// ...
$handler = new \Holdmann\Monolog\TelegramAsyncHandler('<token>', <chat_id>, <log_level>);
// ...
```

**Example:**

```php
$log = new \Monolog\Logger('telegram_channel');

$handler = new \Holdmann\Monolog\TelegramAsyncHandler(
    '000000000:XXXXX-xxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    123456789,
    \Monolog\Logger::DEBUG
);
$handler->setFormatter(new \Monolog\Formatter\LineFormatter("%message%", null, true));
$log->pushHandler($handler);

$log->debug('Test message');
```

The above example is using standard LineFormatter from Monolog package. You can write and use your own message formatter for better logs format.

**Example with proxy (for russian servers):**

```php
$handler = new \Holdmann\Monolog\TelegramAsyncHandler('<token>', <chat_id>, <log_level>);
$handler->setProxy('http://username:password@192.168.16.1:80'); // or simply 'http://192.168.16.1:80'
$handler->setFormatter(new \Monolog\Formatter\LineFormatter("%message%", null, true));
```
