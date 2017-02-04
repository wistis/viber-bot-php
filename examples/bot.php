<?php

/**
 * Before you run this example:
 * 1. install monolog/monolog: composer require monolog/monolog
 * 2. copy config.php.dist to config.php: cp config.php.dist config.php
 *
 * @author Novikov Bogdan <hcbogdan@gmail.com>
 */

require_once("../vendor/autoload.php");

use Viber\Bot;
use Viber\Api\Sender;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$config = require('./config.php');
$apiKey = $config['apiKey'];

$botSender = new Sender([
    'name' => 'Reply bot',
    'avatar' => 'https://developers.viber.com/img/devlogo.png',
]);

$log = new Logger('bot');
$log->pushHandler(new StreamHandler('/tmp/bot.log'));

$bot = null;

try {
    $bot = new Bot(['token' => $apiKey]);
    $bot
    ->onConversation(function ($event) use ($bot, $botSender, $log) {
        $log->info('onConversation '. var_export($event, true));
        // this event fires if user open chat, you can return "welcome message"
        // to user, but you can't send more messages
        return (new \Viber\Api\Message\Text())
            ->setSender($botSender)
            ->setText("Can i help you?");
    })
    ->onText('|whois .*|si', function ($event) use ($bot, $botSender, $log) {
        $log->info('onText whois '. var_export($event, true));
        // match by template, for example "whois Bogdan"
        $bot->getClient()->sendMessage(
            (new \Viber\Api\Message\Text())
            ->setSender($botSender)
            ->setReceiver($event->getSender()->getId())
            ->setText("I do not know )")
        );
    })
    ->onText('|.*|s', function ($event) use ($bot, $botSender, $log) {
        $log->info('onText '. var_export($event, true));
        // .* - match any symbols
        $bot->getClient()->sendMessage(
            (new \Viber\Api\Message\Text())
            ->setSender($botSender)
            ->setReceiver($event->getSender()->getId())
            ->setText("HI!")
        );
    })
    ->on(function ($event) {
        return true;
    }, function ($event) use ($log) {
        $log->info('Other event: '. var_export($event, true));
    })
    ->run();
} catch (Exception $e) {
    $log->warning('Exception: ', $e->getMessage());
    if ($bot) {
        $log->warning('Actual sign: '. $bot->getSignHeaderValue());
        $log->warning('Actual body: '. $bot->getInputBody());
    }
}