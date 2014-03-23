<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc\acl;

use umi\hmvc\component\IComponent;

/**
 * Интерфейс разрешения списка ролей для компонента.
 */
interface IComponentRoleResolver
{
    /**
     * Возвращает список разрешенных ролей компонента.
     * @param IComponent $component
     * @return array
     */
    public function getRoleNames(IComponent $component);
}
 