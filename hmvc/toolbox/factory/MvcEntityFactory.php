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
use umi\hmvc\IMvcEntityFactory;
use umi\toolkit\factory\IFactory;
use umi\toolkit\factory\TFactory;

/**
 * Фабрика для создания сущностей, используемых в компоненте MVC.
 */
class MvcEntityFactory implements IMvcEntityFactory, IFactory
{
    use TFactory;

    /**
     * @var string $modelFactoryClass класс фабрики моделей
     */
    public $modelFactoryClass = 'umi\hmvc\toolbox\factory\ModelFactory';
    /**
     * @var string $viewRendererClass класс рендерера шаблонов
     */
    public $viewRendererClass = 'umi\hmvc\view\ViewRenderer';
    /**
     * @var string $controllerFactoryClass класс фабрики контроллеров
     */
    public $controllerFactoryClass = 'umi\hmvc\toolbox\factory\ControllerFactory';
    /**
     * @var string $widgetFactoryClass класс фабрики виджетов
     */
    public $widgetFactoryClass = 'umi\hmvc\toolbox\factory\WidgetFactory';
    /**
     * @var string $viewExtensionFactoryClass класс фабрики расширений View
     */
    public $viewExtensionFactoryClass = 'umi\hmvc\toolbox\factory\ViewExtensionFactory';
    /**
     * @var string $componentClass MVC компонент по умолчанию
     */
    public $componentClass = 'umi\hmvc\component\Component';
    /**
     * @var string $componentResponseFactoryClass фабрика результатов работы компонента
     */
    public $componentResponseFactoryClass = 'umi\hmvc\toolbox\factory\ComponentResponseFactory';

    /**
     * {@inheritdoc}
     */
    public function createControllerFactory(IComponent $component, array $controllerList)
    {
        return $this->getPrototype(
            $this->controllerFactoryClass,
            ['umi\hmvc\controller\IControllerFactory']
        )
            ->createInstance([$component, $controllerList]);
    }

    /**
     * {@inheritdoc}
     */
    public function createWidgetFactory(IComponent $component, array $widgetList)
    {
        return $this->getPrototype(
            $this->widgetFactoryClass,
            ['umi\hmvc\widget\IWidgetFactory']
        )
            ->createInstance([$component, $widgetList]);
    }

    /**
     * {@inheritdoc}
     */
    public function createModelFactory(array $options)
    {
        return $this->getPrototype(
            $this->modelFactoryClass,
            ['umi\hmvc\model\IModelFactory']
        )
            ->createInstance([$options]);
    }

    /**
     * {@inheritdoc}
     */
    public function createViewRenderer(array $options)
    {
        return $this->getPrototype(
            $this->viewRendererClass,
            ['umi\hmvc\view\IViewRenderer']
        )
            ->createInstance([$options]);
    }

    /**
     * {@inheritdoc}
     */
    public function createComponent($name, $path, array $options)
    {
        $componentClass = isset($options['componentClass']) ? $options['componentClass'] : $this->componentClass;
        unset($options['componentClass']);

        return $this->getPrototype(
            $componentClass,
            ['umi\hmvc\component\IComponent']
        )
            ->createInstance([$name, $path, $options]);
    }

}