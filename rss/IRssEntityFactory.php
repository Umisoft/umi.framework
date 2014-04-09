<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\rss;

/**
 * Фабрика для создания RSS сущностей.
 */
interface IRssEntityFactory
{
    /**
     * Создает RSS-ленту.
     * @param string $url абсолютный URL проекта
     * @param string $title заголовок ленты
     * @param string $description описание ленты
     * @return IRssFeed
     */
    public function createFeed($url, $title, $description);

    /**
     * Создаёт RssFeed на основе XML RSS-ленты.
     * @param string $xml XML RSS-ленты
     * @return IRssFeed
     */
    public function createFeedFromXml($xml);

    /**
     * Создает элемент ленты.
     * @return IRssItem
     */
    public function createItem();
}
 