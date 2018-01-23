#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\TelegramLog as Log;

$config = require 'config.php';

define('API_KEY', $config['API_KEY']);
define('REPORT_CHAT_ID', $config['REPORT_CHAT_ID']);

$BOT_NAME = $config['BOT_NAME'];

$mysql_credentials = [
    'host'     => $config['MYSQL_HOST'],
    'user'     => $config['MYSQL_USER'],
    'password' => $config['MYSQL_PASS'],
    'database' => $config['MYSQL_DB'],
];

$ADMIN_ID = $config['ADMIN_ID'];

$commands = [
    '/parse',
    // "/echo I'm a bot!",
];

try {
    $telegram = new Longman\TelegramBot\Telegram(API_KEY, $BOT_NAME);

    $telegram->enableMySQL($mysql_credentials);
    $telegram->enableLimiter();

    Log::initErrorLog(__DIR__ . '/logs/error.log');
    Log::initDebugLog(__DIR__ . '/logs/debug.log');
    Log::initUpdateLog(__DIR__ . '/logs/update.log');

    $telegram->enableAdmin($ADMIN_ID);

    // $telegram->addCommandsPath(BASE_COMMANDS_PATH . '/SystemCommands');

    // $serverResponse = $telegram->handleGetUpdates();

    // $telegram->runCommands($commands);


    $parser = new \Longman\TelegramBot\Commands\SystemCommands\ParseCommand($telegram);
    $parsedData = $parser->execute();

    if ($parsedData) {
        $result = \Longman\TelegramBot\Request::sendMessage($parsedData);

        if ($result->isOk()) {
            echo 'Message sent succesfully';
        } else {
            echo 'Sorry message not sent';
        }
    }

} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    echo $e;
}
