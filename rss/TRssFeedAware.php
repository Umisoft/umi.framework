<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\rss;

use umi\toolkit\exception\RequiredDependencyException;

/**
 * Трейт для поддержки создания RSS лент.
 */
trait TRssFeedAware
{
    /**
     * @var IRssEntityFactory $traitRssEntityFactory
     */
    private $traitRssEntityFactory;

    /**
     * @see IRssFeedAware::setRssEntityFactory
     */
    public function setRssEntityFactory(IRssEntityFactory $rssEntityFactory)
    {
        $this->traitRssEntityFactory = $rssEntityFactory;
    }

    /**
     * Создает RSS-ленту.
     * @param string $url абсолютный URL проекта
     * @param string $title заголовок ленты
     * @param string $description описание ленты
     * @return IRssFeed
     */
    protected function createRssFeed($url, $title, $description)
    {
        return $this->getRssEntityFactory()->createFeed($url, $title, $description);
    }

    /**
     * Создает элемент ленты.
     * @return IRssItem
     */
    protected function createRssItem()
    {
        return $this->getRssEntityFactory()->createItem();
    }

    /**
     * Создание RssFeed на основе XML RSS-ленты
     * @param string $xml
     * @return IRssFeed
     */
    protected function createRssFeedFromXml($xml)
    {
        return $this->getRssEntityFactory()->createFeedFromXml($xml);
    }

    /**
     * Возвращает фабрику RSS-сущностей.
     * @throws RequiredDependencyException если фабрика не внедрена
     * @return IRssEntityFactory
     */
    private function getRssEntityFactory()
    {
        if (!$this->traitRssEntityFactory) {
            throw new RequiredDependencyException(sprintf(
                'Entity factory is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->traitRssEntityFactory;
    }
}
 