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
 * RSS-лента.
 */
interface IRssFeed
{
    /**
     * Создаёт RSS-ленту.
     * @param string $url абсолютный URL проекта
     * @param string $title наименование RSS-ленты
     * @param string $description описание RSS-ленты
     */
    public function __construct($url, $title, $description);

    /**
     * Добавляет элемент в ленту.
     * @return IRssItem
     */
    public function addItem();

    /**
     * Генерирует XML ленты.
     * @return string
     */
    public function __toString();

    /**
     * Устанавливает заголовок RSS-ленты.
     * @param $title
     * @return self
     */
    public function setTitle($title);

    /**
     * Устанавливает URL проекта.
     * @param $url
     * @return self
     */
    public function setUrl($url);

    /**
     * Устанавливает описание RSS-ленты.
     * @param $description
     * @return self
     */
    public function setDescription($description);

    /**
     * Возвращает заголовок RSS-ленты.
     * @return string
     */
    public function getTitle();

    /**
     * Возвращает URL проекта.
     * @return mixed
     */
    public function getUrl();

    /**
     * Возвращает описание RSS-ленты.
     * @return string
     */
    public function getDescription();

    /**
     * Возвращает список элементов RSS-ленты.
     * @return RssItem[]
     */
    public function getRssItems();
}
 