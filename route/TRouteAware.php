<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\route;

use umi\route\exception\RequiredDependencyException;

/**
 * Трейт для внедрения поддержки маршрутизации.
 */
trait TRouteAware
{
    /**
     * @var IRouteFactory $traitRouteFactory фабрика
     */
    private $traitRouteFactory;

    /**
     * @see IRouteAware::setRouteFactory()
     */
    public function setRouteFactory(IRouteFactory $factory)
    {
        $this->traitRouteFactory = $factory;
    }

    /**
     * Создает маршрутизатор на основе конфигурации.
     * @param array $config конфигурация
     * @return IRouter
     */
    protected function createRouter(array $config)
    {
        return $this->getRouterFactory()
            ->createRouter($config);
    }

    /**
     * Возвращает фабрику для создания маршрутизаторов.
     * @throws RequiredDependencyException если фабрика не была внедрена
     * @return IRouteFactory
     */
    private function getRouterFactory()
    {
        if (!$this->traitRouteFactory) {
            throw new RequiredDependencyException(sprintf(
                'Route factory is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->traitRouteFactory;
    }
}
