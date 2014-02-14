<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc\toolbox;

use umi\hmvc\dispatcher\IDispatcher;
use umi\hmvc\IMvcEntityFactoryAware;
use umi\hmvc\IMvcEntityFactory;
use umi\toolkit\exception\UnsupportedServiceException;
use umi\toolkit\toolbox\IToolbox;
use umi\toolkit\toolbox\TToolbox;

/**
 * Инструменты для создания иерархической MVC-структуры приложений.
 */
class HmvcTools implements IToolbox
{
    /**
     * Имя набора инструментов.
     */
    const NAME = 'hmvc';

    use TToolbox;

    /**
     * @var string $mvcEntityFactoryClass фабрика сущностей компонента MVC
     */
    public $mvcEntityFactoryClass = 'umi\hmvc\toolbox\factory\MvcEntityFactory';
    /**
     * @var string $dispatcherClass класс диспетчера MVC-компонентов
     */
    public $dispatcherClass = 'umi\hmvc\dispatcher\Dispatcher';

    /**
     * Конструктор.
     */
    public function __construct()
    {
        $this->registerFactory(
            'MvcEntity',
            $this->mvcEntityFactoryClass,
            ['umi\hmvc\IMvcEntityFactory']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getService($serviceInterfaceName, $concreteClassName)
    {
        switch ($serviceInterfaceName) {
            case 'umi\hmvc\IMvcEntityFactory':
                return $this->getMvcEntityFactory();
            case 'umi\hmvc\dispatcher\IDispatcher':
                return $this->getDispatcher();
        }
        throw new UnsupportedServiceException($this->translate(
            'Toolbox "{name}" does not support service "{interface}".',
            ['name' => self::NAME, 'interface' => $serviceInterfaceName]
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function injectDependencies($object)
    {
        if ($object instanceof IMvcEntityFactoryAware) {
            $object->setMvcEntityFactory($this->getMvcEntityFactory());
        }
    }

    /**
     * Возвращает фабрику MVC слоев.
     * @return IMvcEntityFactory
     */
    protected function getMvcEntityFactory()
    {
        return $this->getFactory('MvcEntity');
    }

    /**
     * Возвращает диспетчер MVC-компонентов.
     * @return IDispatcher
     */
    protected function getDispatcher()
    {
        return $this->getPrototype(
            $this->dispatcherClass,
            ['umi\hmvc\dispatcher\IDispatcher']
        )
            ->createInstance();
    }

}