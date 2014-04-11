<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */
namespace umi\rss\toolbox\factory;

use DateTime;
use Exception;
use SimpleXMLElement;
use umi\rss\exception\RuntimeException;
use umi\rss\IRssFeed;
use umi\toolkit\factory\IFactory;
use umi\toolkit\factory\TFactory;
use umi\rss\IRssEntityFactory;

/**
 * Фабрика для создания RSS сущностей.
 */
class RssEntityFactory implements IRssEntityFactory, IFactory
{

    use TFactory;

    /**
     * @var string $rssFeedClass класс для создания RSS ленты
     */
    public $rssFeedClass = 'umi\rss\RssFeed';
    /**
     * @var string $rssItemClass класс для создания RSS-элемента
     */
    public $rssItemClass = 'umi\rss\RssItem';

    /**
     * {@inheritdoc}
     */
    public function createFeed($url, $title, $description)
    {
        return $this->getPrototype(
            $this->rssFeedClass,
            ['umi\rss\IRssFeed']
        )
            ->createInstance([$url, $title, $description]);
    }

    /**
     * {@inheritdoc}
     */
    public function createFeedFromXml($xml)
    {
        try {
            $rssElement = new SimpleXMLElement($xml);
        } catch (Exception $e) {
            throw new RuntimeException('Cannot create RSS feed from xml. XML is not wellformed.');
        }

        if (!isset($rssElement->channel)) {
            throw new RuntimeException('Cannot create RSS feed. Channel section is not specified.');
        }

        if (!isset($rssElement->channel->link)) {
            throw new RuntimeException('Cannot create RSS feed. Channel link is not specified.');
        }
        if (!isset($rssElement->channel->title)) {
            throw new RuntimeException('Cannot create RSS feed. Channel title is not specified.');
        }
        if (!isset($rssElement->channel->description)) {
            throw new RuntimeException('Cannot create RSS feed. Channel description is not specified.');
        }

        $rssFeed = $this->createFeed(
            (string) $rssElement->channel->link,
            (string) $rssElement->channel->title,
            (string) $rssElement->channel->description
        );

        $items = $rssElement->channel->item;

        foreach ($items as $item) {
            $this->createItemFromXml($item, $rssFeed);
        }

        return $rssFeed;
    }

    /**
     * {@inheritdoc}
     */
    public function createItem()
    {
        return $this->getPrototype(
            $this->rssItemClass,
            ['umi\rss\IRssItem']
        )
            ->createInstance();
    }

    /**
     * Создаёт элемент на основе XML RSS-ленты
     * @param SimpleXMLElement $item
     * @param IRssFeed $rssFeed
     * @throws RuntimeException
     */
    protected function createItemFromXml($item, $rssFeed)
    {
        if (!isset($item->title) && !isset($item->description)) {
            throw new RuntimeException('Cannot create RSS item. Item title or description is not specified.');
        }

        $newItem = $rssFeed->addItem();

        if (isset($item->title)) {
            $newItem->setTitle($item->title);
        }
        if (isset($item->link)) {
            $newItem->setUrl($item->link);
        }
        if (isset($item->description)) {
            $newItem->setContent($item->description);
        }
        if (isset($item->pubDate)) {
            $pubDate = new DateTime((string)$item->pubDate);
            $newItem->setDate($pubDate);
        }
    }

}

 