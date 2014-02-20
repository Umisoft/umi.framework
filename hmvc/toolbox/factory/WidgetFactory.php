<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc\toolbox\factory;

use umi\hmvc\component\IComponent;
use umi\hmvc\exception\OutOfBoundsException;
use umi\hmvc\exception\RuntimeException;
use umi\hmvc\exception\UnexpectedValueException;
use umi\hmvc\widget\IWidget;
use umi\hmvc\widget\IWidgetFactory;
use umi\hmvc\model\IModelAware;
use umi\hmvc\model\IModelFactory;
use umi\toolkit\factory\IFactory;
use umi\toolkit\factory\TFactory;
use umi\toolkit\prototype\IPrototype;

/**
 * Фабрика виджетов для компонента.
 */
class WidgetFactory implements IWidgetFactory, IFactory, IModelAware
{

    use TFactory;

    /**
     * @var array $widgetList список виджетов компонента
     */
    protected $widgetList = [];
    /**
     * @var IModelFactory $modelFactory фабрика моделей
     */
    protected $modelFactory;
    /**
     * @var IComponent $component компонент
     */
    protected $component;

    /**
     * Конструктор.
     * @param IComponent $component
     * @param array $widgetList список виджетов в формате ['widgetName' => 'widgetClassName', ...]
     */
    public function __construct(IComponent $component, array $widgetList)
    {
        $this->component = $component;
        $this->widgetList = $widgetList;
    }

    /**
     * {@inheritdoc}
     */
    public function createWidget($name, $params = [])
    {
        if (!$this->hasWidget($name)) {
            throw new OutOfBoundsException($this->translate(
                'Cannot create "{name}" widget. Widget is not registered in component "{component}".',
                [
                    'name' => $name,
                    'component' => $this->component->getPath()
                ]
            ));
        }

        return $this->createWidgetByClass($this->widgetList[$name], $params);
    }

    /**
     * {@inheritdoc}
     */
    public function hasWidget($name)
    {
        return isset($this->widgetList[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function setModelFactory(IModelFactory $factory)
    {
        $this->modelFactory = $factory;
    }

    /**
     * Создает виджет заданного класса.
     * @param string $class класс виджета
     * @param array $params параметры вызова виджета
     * @throws UnexpectedValueException если виджет не callable
     * @return IWidget
     */
    protected function createWidgetByClass($class, $params = [])
    {
        $widget = $this->getPrototype(
            $class,
            ['umi\hmvc\widget\IWidget'],
            function (IPrototype $prototype)
            {
                /** @noinspection PhpParamsInspection */
                if (!is_callable($prototype->getPrototypeInstance())) {
                    throw new UnexpectedValueException(
                        $this->translate(
                            'Widget "{class}" should be callable.',
                            ['class' => $prototype->getClassName()]
                        )
                    );
                }
                $prototype->registerConstructorDependency(
                    'umi\hmvc\model\IModel',
                    function ($concreteClassName) {
                        if ($this->modelFactory) {
                            return $this->modelFactory->createByClass($concreteClassName);
                        }

                        return null;
                    }
                );
            }
        )
            ->createInstance([], $params);

        if ($widget instanceof IModelAware) {
            $widget->setModelFactory($this->modelFactory);
        }

        return $widget;
    }
}
 