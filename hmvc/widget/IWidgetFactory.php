<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc\widget;

use umi\hmvc\exception\OutOfBoundsException;

/**
 * Интерфейс фабрики виджетов для компонента.
 */
interface IWidgetFactory
{

    /**
     * Создает виджет по имени.
     * @param string $name имя виджета
     * @param array $params параметры вызова виджета
     * @throws OutOfBoundsException если виджет не существует
     * @return IWidget
     */
    public function createWidget($name, $params = []);

    /**
     * Проверяет существует ли виджет.
     * @param string $name имя виджета
     * @return bool
     */
    public function hasWidget($name);

}
 