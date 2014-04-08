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
 * Интерфейс для поддержки создания RSS лент.
 */
interface IRssFeedAware
{
    /**
     * Устанавливает фабрику сущностей RSS.
     * @param IRssEntityFactory $rssEntityFactory
     */
    public function setRssEntityFactory(IRssEntityFactory $rssEntityFactory);
}
 