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
use XMLWriter;

class RssFeed implements IRssFeed, IRssFeedAware
{
    use TRssFeedAware;

    /**
     * URL проекта.
     * @var string $url
     */
    protected $url;

    /**
     * Заголовок RSS-ленты.
     * @var  $title
     */
    protected $title;
    /**
     * Описание RSS-ленты.
     * @var  $description
     */
    protected $description;
    /**
     * Элементы RSS-ленты.
     * @var RssItem[] $items
     */
    protected $rssItems = [];

    /**
     * {@inheritdoc}
     */
    public function __construct($url, $title, $description)
    {
        $this->url = $url;
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function addItem(
        $url,
        $title,
        $content,
        DateTime $date
    ) {
        $this->rssItems[] = $this->createRssItem($url, $title, $content, $date);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getRssItems()
    {
        return $this->rssItems;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->startDocument('1.0', 'utf-8');

        $xmlWriter->startElement('rss');
        $xmlWriter->writeAttribute('version', '2.0');

        $xmlWriter->startElement('channel');
        $xmlWriter->writeElement('title', $this->title);
        $xmlWriter->writeElement('link', $this->url);
        $xmlWriter->writeElement('description', $this->description);

        foreach ($this->rssItems as $item) {
            $this->generateRssItem($xmlWriter, $item);
        }

        $xmlWriter->endElement();
        $xmlWriter->endElement();
        $xmlWriter->endDocument();

        return $xmlWriter->outputMemory();
    }

    /**
     * Генерирует XML для элемента RSS.
     * @param XMLWriter $xmlWriter
     * @param RssItem $item
     */
    protected function generateRssItem(XMLWriter $xmlWriter, RssItem $item)
    {
        $xmlWriter->startElement('item');
        $xmlWriter->writeElement('title', $item->getTitle());
        $xmlWriter->writeElement(
            'link',
            $item->getUrl()
        );
        $xmlWriter->startElement('description');
        $xmlWriter->writeCdata($item->getContent());
        $xmlWriter->endElement();
        $xmlWriter->writeElement('pubDate', $item->getDate()->format(DateTime::RSS));
        $xmlWriter->endElement();
    }
}
 