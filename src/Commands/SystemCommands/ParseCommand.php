<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Parser;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Html2Text\Html2Text;

class ParseCommand extends SystemCommand
{
    protected $name = 'parse';
    protected $description = 'Try to parse forum feed';
    protected $usage = '/parse';
    protected $version = '0.0.1';
    protected $conversation;
    protected $need_mysql = true;

    public function execute()
    {
//        $message = $this->getMessage();
//        $chat_id = $message->getChat()->getId();
//        $chat_id = 108894177;
        $chat_id = 329853602;

        $text    = 'Parse result:';

        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];

        $parsedData = new Parser();

        $postExists = $this->getForumPost($parsedData->updated) !== 0;

        if ($parsedData->link && !$postExists) {
            $this->insertPost( $parsedData->updated );

            $data = [
                'chat_id' => $chat_id,
                'text'    => @Html2Text::convert( $parsedData->link . PHP_EOL . $parsedData->content),
                'disable_web_page_preview' => true,
                'disable_notification' => false
            ];

            //return Request::sendMessage($data);
            return $data;
        }

        return false;
    }

    public function getForumPost($date = null, $limit = 1)
    {
        $DB = new DB();
        $pdo = $DB->getPdo();

        if (!$DB::isDbConnected()) {
            return false;
        }

        try {
            $sql = '
                SELECT *
                FROM `forum_posts`
                WHERE `updated_at` = :updated_at
                ORDER BY `id` DESC
            ';

            if ($limit !== null) {
                $sql .= ' LIMIT :limit';
            }

            $sth = $pdo->prepare($sql);

            $sth->bindValue(':updated_at', $date);

            if ($limit !== null) {
                $sth->bindValue(':limit', $limit, \PDO::PARAM_INT);
            }

            $sth->execute();

            return $sth->rowCount();
        } catch (\PDOException $e) {
            throw new TelegramException($e->getMessage());
        }
    }

    public function insertPost($date = null)
    {
        $DB = new DB();
        $pdo = $DB->getPdo();

        if (!$DB::isDbConnected()) {
            return false;
        }

        try {
            $sth = $pdo->prepare('
                INSERT INTO `forum_posts`
                (`updated_at`)
                VALUES
                (:updated_at)
                ON DUPLICATE KEY UPDATE
                    `updated_at` = VALUES(`updated_at`)
            ');

            $sth->bindValue(':updated_at', $date);

            $status = $sth->execute();
        } catch (\PDOException $e) {
            throw new TelegramException($e->getMessage());
        }

        return $status;
    }
}
