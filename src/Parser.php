<?php

namespace Longman\TelegramBot;

class Parser
{
    const FORUM_XML_URL = 'http://forum.awd.ru/feed.php?f=326&t=332244';
    //const FORUM_XML_URL = '/home/ubuntu/forum-bot/test.xml';
    const PATTERN_CITY = 'Питер|Спб|Санкт|Петербург|Владивосток|Влади';
    const PATTERN_AVAILABILITY = '(место есть)|(есть мест)|(места есть)|(много мест)|(мест много)|(записался)|(записалась)|(есть \S* мест)';

    public $content;
    public $updated;
    public $link;

    public function __construct()
    {
        $xml = new \DOMDocument();
        $xml->load(self::FORUM_XML_URL);
        $contentEntries = $xml->getElementsByTagName('entry')->item(0);
        $result = null;

//        foreach ($contentEntries as $node) {

        $node = $contentEntries; // read only latest post
            $updated = $node->getElementsByTagName('updated')->item(0)->textContent;
            $content = $node->getElementsByTagName('content')->item(0)->textContent;
            $link = $node->getElementsByTagName('link')->item(0)->getAttribute('href');
            preg_match('/'. self::PATTERN_CITY . '/im', $content, $matchCity);
            if (!empty($matchCity)) {
                preg_match('/'. self::PATTERN_AVAILABILITY . '/im', $content, $matchAvailability);
                if (!empty($matchAvailability)) {
                    $this->content = $content;
                    $this->updated = $updated;
                    $this->link = $link;
                }
            }

//        }
    }
}