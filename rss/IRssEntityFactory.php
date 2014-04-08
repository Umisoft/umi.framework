<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\rss;

use DateTime;

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
     * Загружает RSS-ленту по URL.
     * @param string $url абсолютный URL ленты
     * @return IRssFeed
     */
    public function loadFeed($url);

    /**
     * Создает элемент ленты.
     * @param string $url абсолютный URL элемента
     * @param string $title заголовок элемента
     * @param string $content контент элемента
     * @param DateTime $date дата публикации элемента
     * @return IRssItem
     */
    public function createItem($url, $title, $content, DateTime $date);
}
 