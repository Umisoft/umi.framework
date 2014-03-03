<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc;

use umi\hmvc\component\IComponent;
use umi\hmvc\controller\IControllerFactory;
use umi\hmvc\exception\RequiredDependencyException;
use umi\hmvc\widget\IWidgetFactory;
use umi\hmvc\model\IModelFactory;
use umi\hmvc\view\IViewRenderer;

/**
 * Трейт для внедрения возможости создания сущностей для компонента MVC.
 */
trait TMvcEntityFactoryAware
{
    /**
     * @var IMvcEntityFactory $traitMvcEntityFactory
     */
    private $traitMvcEntityFactory;

    /**
     * @see IMvcEntityFactoryAware::setMvcEntityFactory()
     */
    public function setMvcEntityFactory(IMvcEntityFactory $factory)
    {
        $this->traitMvcEntityFactory = $factory;
    }

    /**
     * Создает фабрику контроллеров для компонента.
     * @param IComponent $component
     * @param array $controllerList список контроллеров в формате ['controllerName' => 'controllerClassName', ...]
     * @return IControllerFactory
     */
    protected function createMvcControllerFactory(IComponent $component, array $controllerList)
    {
        return $this->getMvcEntityFactory()
            ->createControllerFactory(
                $component,
                $controllerList
            );
    }

    /**
     * Создает фабрику виджетов для компонента.
     * @param IComponent $component
     * @param array $widgetList список виджетов в формате ['widgetName' => 'widgetClassName', ...]
     * @return IWidgetFactory
     */
    protected function createMvcWidgetFactory(IComponent $component, array $widgetList)
    {
        return $this->getMvcEntityFactory()
            ->createWidgetFactory(
                $component,
                $widgetList
            );
    }

    /**
     * Создает фабрику моделей.
     * @param array $options опции
     * @return IModelFactory
     */
    protected function createMvcModelFactory(array $options)
    {
        return $this->getMvcEntityFactory()
            ->createModelFactory($options);
    }

    /**
     * Создает рендерер шаблонов.
     * @param array $options опции
     * @return IViewRenderer
     */
    protected function createMvcViewRenderer(array $options)
    {
        return $this->getMvcEntityFactory()
            ->createViewRenderer($options);
    }

    /**
     * Создает MVC компонент.
     * @param string $name имя компонента
     * @param string $path иерархический путь компонента
     * @param array $options конфигурация
     * @return IComponent
     */
    protected function createMvcComponent($name, $path, array $options)
    {
        return $this->getMvcEntityFactory()
            ->createComponent($name, $path, $options);
    }

    /**
     * Возвращает фабрику слоев MVC.
     * @throws RequiredDependencyException если фабрика не внедрена
     * @return IMvcEntityFactory
     */
    private function getMvcEntityFactory()
    {
        if (!$this->traitMvcEntityFactory) {
            throw new RequiredDependencyException(sprintf(
                'MVC entity factory is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->traitMvcEntityFactory;
    }

}
