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
 * Элемент RSS-ленты.
 */
interface IRssItem
{
    /**
     * Устанавливает заголовок элемента.
     * @param string $title
     * @return self
     */
    public function setTitle($title);

    /**
     * Устанавливает контент элемента.
     * @param string $content
     * @return self
     */
    public function setContent($content);

    /**
     * Устанавливает дату публикации элемента.
     * @param DateTime|null $date
     * @return self
     */
    public function setDate(DateTime $date = null);

    /**
     * Устанавливает абсолютный URL элемента.
     * @param $url
     * @return self
     */
    public function setUrl($url);

    /**
     * Возвращает заголовок элемента.
     * @return string
     */
    public function getTitle();

    /**
     * Возвращает контент элемента.
     * @return string
     */
    public function getContent();

    /**
     * Возвращает дату публикации элемента.
     * @return DateTime|null
     */
    public function getDate();

    /**
     * Возвращает абсолютный URL элемента.
     * @return string
     */
    public function getUrl();
}
 