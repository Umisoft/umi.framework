<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\route;

use umi\i18n\ILocalizable;
use umi\i18n\TLocalizable;
use umi\route\exception\RuntimeException;
use umi\route\result\IRouteResultBuilder;
use umi\route\result\RouteResultBuilder;
use umi\route\type\IRoute;

/**
 * Маршрутизатор.
 */
class Router implements IRouter, ILocalizable
{
    use TLocalizable;

    /**
     * @var array $params параметры
     */
    protected $params = [];
    /**
     * @var IRoute[] $routes массив правил маршрутизации
     */
    protected $routes = [];

    /**
     * Конструктор.
     * @param IRoute[] $routes массив правил маршрутизации
     */
    public function __construct(array $routes = [])
    {
        $this->routes = $routes;
        uasort($this->routes, function(IRoute $first, IRoute $second){
            if ($first->getPriority() == $second->getPriority()) {
                return 0;
            }
            return ($first->getPriority() < $second->getPriority()) ? -1 : 1;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function match($url, $baseUrl = null)
    {
        $resultBuilder = new RouteResultBuilder();
        $resultBuilder->setUnmatchedUrl($url);

        $this->matchRoutes($this->routes, $url, $resultBuilder, $baseUrl);

        return $resultBuilder->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function assemble($name, array $params = [])
    {
        $url = '';

        $names = explode('/', $name);

        $routes = $this->routes;
        foreach ($names as $name) {
            if (!isset($routes[$name])) {
                throw new RuntimeException($this->translate(
                    'Route "{name}" not found.',
                    ['name' => $name]
                ));
            }

            $url .= $routes[$name]->assemble($params);
            $routes = $routes[$name]->getSubRoutes();
        }

        return $url;
    }

    /**
     * Рекурсивно проверяет соответствие url маршрутам.
     * Если маршрут подошел, то пробует подобрать соответствие
     * в дочерних к нему маршрутах.
     * @param IRoute[] $routes правила маршрутизации
     * @param string $url проверяемый URL
     * @param IRouteResultBuilder $resultBuilder
     * @param string|null $baseUrl базовый URL
     * @return bool
     */
    protected function matchRoutes(array $routes, $url, IRouteResultBuilder $resultBuilder, $baseUrl = null)
    {
        foreach ($routes as $name => $route) {

            if (false === ($matchedLength = $route->match($url, $baseUrl))) {
                continue;
            }

            $resultBuilder->addMatch($name, $route->getParams(), substr($url, 0, $matchedLength));
            $url = substr($url, $matchedLength);
            $resultBuilder->setUnmatchedUrl($url ? : null);

            if (!$url || $this->matchRoutes($route->getSubRoutes(), $url, $resultBuilder)) {
                return true;
            }

            break;
        }

        return false;
    }
}