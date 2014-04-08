<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\rss\toolbox;

use umi\rss\IRssEntityFactory;
use umi\toolkit\toolbox\IToolbox;
use umi\toolkit\toolbox\TToolbox;
use umi\rss\IRssFeedAware;

/**
 * Инструментарий для работы с RSS.
 */
class RssTools implements IToolbox
{
    /**
     * Имя набора инструментов
     */
    const NAME = 'rss';

    use TToolbox;

    /**
     * @var string $rssEntityFactoryClass класс для создания фабрики RSS-сущностей
     */
    public $rssEntityFactoryClass = 'umi\rss\toolbox\factory\RssEntityFactory';

    /**
     * Конструктор.
     */
    public function __construct()
    {
        $this->registerFactory(
            'rss',
            $this->rssEntityFactoryClass,
            ['umi\rss\IRssEntityFactory']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function injectDependencies($object)
    {
        if ($object instanceof IRssFeedAware) {
            $object->setRssEntityFactory($this->getRssEntityFactory());
        }
    }

    /**
     * Создает и возвращает фабрику RSS сущностей.
     * @return IRssEntityFactory
     */
    protected function getRssEntityFactory()
    {
        return $this->getFactory('rss');
    }
}
