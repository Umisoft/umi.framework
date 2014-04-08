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
    public function loadFeed($url)
    {
        // TODO:
    }

    /**
     * {@inheritdoc}
     */
    public function createItem($url, $title, $content, DateTime $date)
    {
        return $this->getPrototype(
            $this->rssItemClass,
            ['umi\rss\IRssItem']
        )
            ->createInstance([$url, $title, $content, $date]);
    }

}

 